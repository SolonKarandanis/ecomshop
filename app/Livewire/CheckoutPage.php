<?php

namespace App\Livewire;

use App\Dtos\CheckoutDTO;
use App\Enums\MessageSeverityEnum;
use App\Exceptions\EmptyCartException;
use App\Exceptions\OrderException;
use App\Exceptions\PaymentException;
use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\UiService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

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
    protected UiService $uiService;
    public ?Cart $cart = null;

    public function boot(
        OrderService $orderService,
        CartService $cartService,
        UiService $uiService,
    ): void{
        $this->orderService = $orderService;
        $this->cartService = $cartService;
        $this->uiService = $uiService;
    }

    public function mount(): void
    {
        if (Gate::denies('buyer-action')) {
            $this->redirect(route('home'));
            return;
        }
        $this->cart = $this->cartService->getCart();
    }

    public function save(){
        $request = new CheckoutRequest();
        $request->merge([
            'firstName'     => $this->firstName,
            'lastName'      => $this->lastName,
            'phone'         => $this->phone,
            'address'       => $this->address,
            'city'          => $this->city,
            'country'       => $this->country,
            'zipCode'       => $this->zipCode,
            'paymentMethod' => $this->paymentMethod,
        ]);
        $this->validate($request->rules());
        $dto = CheckoutDTO::fromRequest($request);
        $title = __('messages.checkout.title');
        $error = __('messages.checkout.error');
        try {
            $redirect_url = $this->orderService->checkout($dto);
            return redirect($redirect_url);
        } catch (EmptyCartException $e) {
            $this->handleError($title, __('messages.checkout.empty_cart'), $e);
            return redirect()->route('cart');
        } catch (OrderException|PaymentException $e) {
            $this->handleError($title, $error, $e);
            return redirect()->route('cart');
        } catch (Throwable $e) {
            $this->handleError($title, $error, $e);
            return redirect()->route('cart');
        }
    }

    protected function handleError(string $msgTitle, string $msgFail, Throwable $e): void
    {
        Log::error($e->getMessage());
        $this->uiService->showMessage(
            MessageSeverityEnum::ERROR,
            $msgTitle,
            $msgFail
        );
    }

    public function render()
    {
        return view('livewire.checkout-page');
    }
}
