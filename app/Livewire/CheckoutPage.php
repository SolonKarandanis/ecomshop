<?php

namespace App\Livewire;

use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
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
    }
    public function render()
    {
        return view('livewire.checkout-page');
    }
}
