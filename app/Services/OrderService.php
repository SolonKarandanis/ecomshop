<?php

namespace App\Services;

use App\Dtos\CheckoutDTO;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\AddressRepository;
use App\Repositories\OrderRepository;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly AddressRepository $addressRepository,
        private readonly CartService $cartService,
        private readonly StripeService $stripeService
    ){}

    public function checkout(CheckoutDTO $dto):string{
        $cart = $this->cartService->getCart();
        $line_items=[];
        $order_items=[];
        foreach ($cart->cartItems as $cartItem){
            $line_items[]=[
                'price_data'=>[
                    'currency'=>config('app.currency'),
                    'unit_amount'=>$cartItem->total_price * 100, //stripe wants unit amount in cents
                    'product_data'=>[
                        'name'=>$cartItem->product->name,
                    ]
                ],
                'quantity'=>$cartItem->quantity,
            ];
            $order_items[]=[new OrderItem($cartItem)];
        }
        $order = new Order();
        $order->user_id = auth()->user()->id;
        $order->grand_total= $cart->total_price;
        $order->payment_method = $dto->getPaymentMethod();
        $order->payment_status = 'pending';
        $order->order_status=OrderStatusEnum::Draft->value;
        $order->currency = 'eur';
        $order->shipping_amount=0;
        $order->shipping_method='none';
        $order->notes='Order placed'.auth()->user()->name;
        $order->setRelation('orderItems',$order_items);

        $redirect_url = '';
        if($validated['paymentMethod']=='stripe'){
            Stripe::setApiKey(config('app.stripe_secret_key'));
            $sessionCheckout = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => auth()->user()->email,
                'line_items'=>$line_items,
                'mode'=>'payment',
                'success_url'=>route('success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'=>route('cancel'),
            ]);
            $redirect_url=$sessionCheckout->url;
        }

        if($validated['paymentMethod']=='cod'){
            $redirect_url=route('success');
        }

        $order->save();

        $this->addressRepository->create($order->id,$dto);

        $this->cartService->clearCart();

        return $redirect_url;
    }
}
