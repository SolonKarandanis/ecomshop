<?php

namespace App\Livewire;

use App\Dtos\UpdateCartItemsDTO;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Livewire\Component;

class CartPage extends Component
{
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

    public function increaseQuantity(string|int $cartItemId): void
    {
        $this->cart = $this->cartService->getCart();
        $cartItem = $this->findCartItem($this->cart, $cartItemId);

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + 1;
            $this->updateQuantity($this->cart,$cartItem, $newQuantity);
        }
    }

    public function decreaseQuantity(string|int $cartItemId): void
    {
        $this->cart = $this->cartService->getCart();
        $cartItem = $this->findCartItem($this->cart, $cartItemId);

        if ($cartItem && $cartItem->quantity > 1) {
            $newQuantity = $cartItem->quantity - 1;
            $this->updateQuantity($this->cart,$cartItem, $newQuantity);
        }
    }

    public function removeItem(string|int $cartItemId ):void{
        $cartIds[]=$cartItemId;
        $this->cartService->removeItemsFromCart($cartIds);
        $this->cart = $this->cartService->getCart();
        $this->dispatch('cartUpdated');
    }

    public function clearCart():void{
        $this->cartService->clearCart();
        $this->dispatch('cartUpdated');
    }

    private function findCartItem($cart, string $cartItemId): ?CartItem
    {
        return $cart->cartItems->first(function ($item) use ($cartItemId) {
            $itemId = $item->id ?? $item->id_from_cookie;
            return (string) $itemId === $cartItemId;
        });
    }

    private function updateQuantity(Cart $cart,CartItem $cartItem, int $newQuantity): void
    {
        $attributes = json_decode($cartItem->attributes, true) ?? [];
        $cartItemId = $cartItem->id ?? $cartItem->id_from_cookie;

        $updateDto = new UpdateCartItemsDTO(
            (string) $cartItemId,
            $cartItem->product_id,
            $newQuantity,
            $attributes
        );
        $this->cartService->updateItemsQuantity($cart,[$updateDto]);
        $this->cart = $this->cartService->getCart();
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}
