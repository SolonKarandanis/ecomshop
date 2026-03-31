<?php

namespace App\Livewire;

use App\Http\Requests\CheckoutRequest;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\Checkout\Session;
use Stripe\Stripe;

#[Title('Checkout')]
class CheckoutPage extends Component
{
    public string $firstName = '';
    public string $lastName = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $zipCode = '';
    public string $paymentMethod = '';
    protected CartService $cartService;
    public ?Cart $cart = null;

    public function boot(
        CartService $cartService
    ): void{
        $this->cartService = $cartService;
    }

    public function mount(): void
    {
        $this->cart = $this->cartService->getCart();
    }

    public function save(){
        $validated = $this->validate((new CheckoutRequest())->rules());
        $cart = $this->cartService->getCart();
        $line_items=[];
        $order_items=[];
        foreach ($cart->cartItems as $cartItem){
            $line_items[]=[
                'price_data'=>[
                    'currency'=>'eur',
                    'unit_amount'=>$cartItem->total_price * 100,
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
        $order->payment_method = $validated['paymentMethod'];
        $order->payment_status = 'pending';
        $order->order_status='draft';
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

        $address = new Address();
        $address->user_id = auth()->user()->id;
        $address->order_id = $order->id;
        $address->first_name = $validated['firstName'];
        $address->last_name = $validated['lastName'];
        $address->phone = $validated['phone'];
        $address->city = $validated['city'];
        $address->street_address = $validated['address'];
        $address->country=$validated['country'];
        $address->postal_code=$validated['zipCode'];
        $address->save();

        $this->cartService->clearCart();

        return redirect($redirect_url);
    }
    public function render()
    {
        return view('livewire.checkout-page');
    }
}
