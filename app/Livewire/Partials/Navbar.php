<?php

namespace App\Livewire\Partials;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class Navbar extends Component
{
    protected CartService $cartService;
    public $total_cart_items = 0;

    public function boot(CartService $cartService){
        $this->cartService = $cartService;
    }
    public function mount(){
        $this->total_cart_items = $this->cartService->getCartItemsCount();
    }

    #[On('cartUpdated')]
    public function cartUpdated(){
        $this->total_cart_items = $this->cartService->getCartItemsCount();
    }
    public function render()
    {
        return view('livewire.partials.navbar');
    }
}
