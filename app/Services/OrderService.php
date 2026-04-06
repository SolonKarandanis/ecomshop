<?php

namespace App\Services;

use App\Dtos\CheckoutDTO;
use App\Dtos\CreateOrderDTO;
use App\Enums\PaymentMethodEnum;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\AddressRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\StripeOrderDetailRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly AddressRepository $addressRepository,
        private readonly PaymentMethodRepository $paymentMethodRepository,
        private readonly StripeOrderDetailRepository $stripeOrderDetailRepository,
        private readonly CartService $cartService,
        private readonly StripeService $stripeService
    ){}

    public function getOrderById(int $orderId):Order{
        return $this->orderRepository->getOrderById($orderId);
    }

    /**
     * @throws \Throwable
     */
    public function checkout(CheckoutDTO $dto):string{
        $line_items=[];
        $order_items=[];
        $paymentMethod=$dto->getPaymentMethod();
        DB::beginTransaction();
        try{
            $cart = $this->cartService->getCart();
            Log::debug('OrderService checkout cartItems count: ', [$cart->cartItems->count()]);
            if (empty($cart->cartItems)) {
                throw new \Exception('Cart is empty');
            }
            foreach ($cart->cartItems as $cartItem){
                $line_item=[
                    'price_data'=>[
                        'currency'=>config('app.currency'),
                        'unit_amount'=>$cartItem->unit_price * 100, //stripe wants unit amount in cents
                        'product_data'=>[
                            'name'=>$cartItem->product->name,
                        ]
                    ],
                    'quantity'=>$cartItem->quantity,
                ];
                $line_items[] = $line_item;
                $orderItem = new OrderItem($cartItem);
                $order_items[]=$orderItem->attributesToArray();
            }

            $paymentMethods = $this->paymentMethodRepository->findAll()->pluck('id', 'resource_key');
            $paymentMethodId=$paymentMethods->get($paymentMethod);
            Log::debug('OrderService creating order');
            $order = $this->createNewOrder($cart->total_price,$paymentMethodId,$order_items);
            Log::debug('OrderService created order ',[$order->id]);

            $redirect_url = '';
            if($paymentMethod==PaymentMethodEnum::STRIPE->value){
                Log::debug('OrderService paymentMethod: Stripe');
                Stripe::setApiKey(config('app.stripe_secret_key'));
                $sessionCheckout = $this->stripeService->createSession($line_items);
                Log::debug('OrderService $sessionCheckout:',[$sessionCheckout->id]);
                $this->stripeOrderDetailRepository->createStripeOrderDetail($order->id,$sessionCheckout->id);
                $redirect_url=$sessionCheckout->url;
            }

            if($paymentMethod==PaymentMethodEnum::CASH_ON_DELIVERY->value){
                Log::debug('OrderService paymentMethod: Cash on delivery');
                $redirect_url=route('success');
            }

            Log::debug('OrderService creating address');
            $this->addressRepository->create($order->id,$dto);
            Log::debug('OrderService created address');

            Log::debug('OrderService clearing cart');
            $this->cartService->clearCart();
            DB::commit();
            Mail::to(request()->user())->send(new OrderPlaced($order));
            return $redirect_url;
        }
        catch (\Exception $exception){
            Log::error($exception);
            DB::rollBack();
            return back()->with('error',$exception->getMessage()? :'Something went wrong!');
        }
    }

    /**
     * @throws \Throwable
     */
    protected function createNewOrder(int $totalPrice, int $paymentMethodId, array $orderItems): Order
    {
        $createOrderDto = new CreateOrderDTO($totalPrice,$paymentMethodId,$orderItems);
        return $this->orderRepository->createOrder($createOrderDto);
    }
}
