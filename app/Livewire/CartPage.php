<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartPage extends Component
{
    protected CartService $cartService;

    public function boot(
        CartService $cartService
    ): void{
        $this->cartService = $cartService;
    }
    public function render()
    {
        $cart = $this->cartService->getCart();
        return view('livewire.cart-page',['cart'=>$cart]);
    }
}
