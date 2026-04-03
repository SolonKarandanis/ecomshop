<?php

namespace App\Services;

use App\Dtos\CheckoutDTO;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\AddressRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentMethodRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly AddressRepository $addressRepository,
        private readonly PaymentMethodRepository $paymentMethodRepository,
        private readonly CartService $cartService,
        private readonly StripeService $stripeService
    ){}

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
            foreach ($cart->cartItems as $cartItem){
                $line_item=[
                    'price_data'=>[
                        'currency'=>config('app.currency'),
                        'unit_amount'=>$cartItem->total_price * 100, //stripe wants unit amount in cents
                        'product_data'=>[
                            'name'=>$cartItem->product->name,
                        ]
                    ],
                    'quantity'=>$cartItem->quantity,
                ];
                $line_items[] = $line_item;
                $order_items[]=[new OrderItem($cartItem)];
            }

            $paymentMethods = $this->paymentMethodRepository->findAll()->pluck('id', 'resource_key');

            $redirect_url = '';
            if($paymentMethod==PaymentMethodEnum::STRIPE->value){
                Stripe::setApiKey(config('app.stripe_secret_key'));
                $sessionCheckout = $this->stripeService->createSession($line_items);
                $redirect_url=$sessionCheckout->url;
            }

            if($paymentMethod==PaymentMethodEnum::CASH_ON_DELIVERY->value){
                $redirect_url=route('success');
            }

            $order = new Order();
            $order->user_id = auth()->user()->id;
            $order->grand_total= $cart->total_price;
            $order->payment_method = $paymentMethod;
            $order->payment_status = 'pending';
            $order->order_status=OrderStatusEnum::Draft->value;
            $order->currency = config('app.currency');
            $order->shipping_amount=0;
            $order->shipping_method='none';
            $order->notes='Order placed'.auth()->user()->name;
            $order->setRelation('orderItems',$order_items);
            $order->save();

            $this->addressRepository->create($order->id,$dto);

            $this->cartService->clearCart();
            DB::commit();
            return $redirect_url;
        }
        catch (\Exception $exception){
            Log::error($exception);
            DB::rollBack();
            return back()->with('error',$exception->getMessage()? :'Something went wrong!');
        }
    }
}
