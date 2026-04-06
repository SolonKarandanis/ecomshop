<?php

namespace App\Livewire;

use App\Dtos\CheckoutDTO;
use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Services\CartService;
use App\Services\OrderService;
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
    public string $country = '';
    public string $zipCode = '';
    public string $paymentMethod = '';
    protected OrderService $orderService;
    protected CartService $cartService;
    public ?Cart $cart = null;

    public function boot(
        OrderService $orderService,
        CartService $cartService
    ): void{
        $this->orderService = $orderService;
        $this->cartService = $cartService;
    }

    public function mount(): void
    {
        $this->cart = $this->cartService->getCart();
    }

    /**
     * @throws \Throwable
     */
    public function save(){
        $validated = $this->validate((new CheckoutRequest())->rules());
        $dto = CheckoutDTO::fromArray($validated);
        $redirect_url = $this->orderService->checkout($dto);
        return redirect($redirect_url);
    }
    public function render()
    {
        return view('livewire.checkout-page');
    }
}
