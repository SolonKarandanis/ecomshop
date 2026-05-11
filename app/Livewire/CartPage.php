<?php

namespace App\Livewire;

use App\Dtos\UpdateCartItemsDTO;
use App\Enums\MessageSeverityEnum;
use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\UiService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Throwable;

class CartPage extends Component
{
    protected CartService $cartService;
    protected UiService $uiService;
    public ?Cart $cart = null;

    public function boot(
        CartService $cartService,
        UiService $uiService
    ): void{
        $this->cartService = $cartService;
        $this->uiService = $uiService;
    }

    public function mount(): void
    {
        $this->cart = $this->cartService->getCart();
    }

    public function increaseQuantity(string|int $cartItemId): void
    {
        $cartItem = $this->getCartItem($cartItemId);
        if ($cartItem) {
            $newQuantity = $cartItem->quantity + 1;
            $this->updateQuantity($this->cart,$cartItem, $newQuantity);
        }
    }

    public function decreaseQuantity(string|int $cartItemId): void
    {
        $cartItem = $this->getCartItem($cartItemId);
        if ($cartItem && $cartItem->quantity > 1) {
            $newQuantity = $cartItem->quantity - 1;
            $this->updateQuantity($this->cart,$cartItem, $newQuantity);
        }
    }

    public function removeItem(string|int $cartItemId ):void{
        $cartIds[]=$cartItemId;
        $title=__('messages.remove_from_cart.title');
        $success=__('messages.remove_from_cart.success');
        $error=__('messages.remove_from_cart.error');
        try {
            $this->cartService->removeItemsFromCart($cartIds);
            $this->handleSuccess('cartUpdated', $title, $success);
        } catch (CartException $e) {
            $this->handleError($title, $error, $e);
        }
        $this->cart = $this->cartService->getCart();
    }

    public function clearCart():void{
        $title=__('messages.clear_cart.title');
        $success=__('messages.clear_cart.success');
        $error=__('messages.clear_cart.error');
        try {
            $this->cartService->clearCart();
            $this->handleSuccess('cartUpdated', $title, $success);
        } catch (CartException $e) {
            $this->handleError($title, $error, $e);
        }
        $this->cart = $this->cartService->getCart();
    }

    private function getCartItem(string|int $cartItemId):CartItem|null{
        $this->cart = $this->cartService->getCart();
       return $this->findCartItem($this->cart, $cartItemId);
    }

    private function findCartItem($cart, string $cartItemId): ?CartItem
    {
        return $cart->cartItems->first(function ($item) use ($cartItemId) {
            $itemId = $item->id ?? $item->id_from_cookie;
            return (string) $itemId === $cartItemId;
        });
    }

    private function updateQuantity(Cart $cart, CartItem $cartItem, int $newQuantity): void
    {
        $attributes = $cartItem->attributes ?? [];
        $cartItemId = $cartItem->id ?? $cartItem->id_from_cookie;

        $updateDto = new UpdateCartItemsDTO(
            (string) $cartItemId,
            $cartItem->product_id,
            $newQuantity,
            $attributes
        );
        $title=__('messages.update_quantity.title');
        $success=__('messages.update_quantity.success');
        $error=__('messages.update_quantity.error');
        try {
            $this->cartService->updateItemsQuantity($cart, [$updateDto]);
            $this->handleSuccess(null, $title, $success);
        } catch (CartException $e) {
            $this->handleError($title, $error, $e);
        }
        $this->cart = $this->cartService->getCart();
    }

    protected function handleSuccess(string|null $dispatchEvent,string $msgTitle,string $msgSuccess):void
    {
        if($dispatchEvent){
            $this->dispatch($dispatchEvent);
        }
        $this->uiService->showMessage(
            MessageSeverityEnum::SUCCESS,
            $msgTitle,
            $msgSuccess
        );
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
        return view('livewire.cart-page');
    }
}
