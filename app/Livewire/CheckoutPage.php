<?php

namespace App\Livewire;

use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Livewire\Attributes\Title;
use Livewire\Component;

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
    }
    public function render()
    {
        return view('livewire.checkout-page');
    }
}
