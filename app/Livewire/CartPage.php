<?php

namespace App\Livewire;

use App\Dtos\UpdateCartItemsDTO;
use App\Enums\MessageSeverityEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\UiService;
use Livewire\Component;

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

    /**
     * @throws \Throwable
     */
    public function increaseQuantity(string|int $cartItemId): void
    {
        $this->cart = $this->cartService->getCart();
        $cartItem = $this->findCartItem($this->cart, $cartItemId);

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + 1;
            $this->updateQuantity($this->cart,$cartItem, $newQuantity);
        }
    }

    /**
     * @throws \Throwable
     */
    public function decreaseQuantity(string|int $cartItemId): void
    {
        $this->cart = $this->cartService->getCart();
        $cartItem = $this->findCartItem($this->cart, $cartItemId);

        if ($cartItem && $cartItem->quantity > 1) {
            $newQuantity = $cartItem->quantity - 1;
            $this->updateQuantity($this->cart,$cartItem, $newQuantity);
        }
    }

    /**
     * @throws \Throwable
     */
    public function removeItem(string|int $cartItemId ):void{
        $cartIds[]=$cartItemId;
        $result=$this->cartService->removeItemsFromCart($cartIds);
        $this->cart = $this->cartService->getCart();
        $title=__('messages.remove_from_cart.title');
        $success=__('messages.remove_from_cart.success');
        $error=__('messages.remove_from_cart.error');
        $this->handleActionResult($result,'cartUpdated',$title,$success,$error);
    }

    /**
     * @throws \Throwable
     */
    public function clearCart():void{
        $result=$this->cartService->clearCart();
        $this->cart = $this->cartService->getCart();
        $title=__('messages.clear_cart.title');
        $success=__('messages.clear_cart.success');
        $error=__('messages.clear_cart.error');
        $this->handleActionResult($result,'cartUpdated',$title,$success,$error);
    }

    private function findCartItem($cart, string $cartItemId): ?CartItem
    {
        return $cart->cartItems->first(function ($item) use ($cartItemId) {
            $itemId = $item->id ?? $item->id_from_cookie;
            return (string) $itemId === $cartItemId;
        });
    }

    /**
     * @throws \Throwable
     */
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
        $result=$this->cartService->updateItemsQuantity($cart,[$updateDto]);
        $this->cart = $this->cartService->getCart();
        $title=__('messages.update_quantity.title');
        $success=__('messages.update_quantity.success');
        $error=__('messages.update_quantity.error');
        $this->handleActionResult($result,null,$title,$success,$error);
    }

    protected function handleActionResult(bool $result,string|null $dispatchEvent,string $msgTitle,string $msgSuccess,string $msgFail):void
    {
        if($result){
            if($dispatchEvent){
                $this->dispatch($dispatchEvent);
            }
            $this->uiService->showMessage(
                MessageSeverityEnum::SUCCESS,
                $msgTitle,
                $msgSuccess
            );
        }
        else{
            $this->uiService->showMessage(
                MessageSeverityEnum::ERROR,
                $msgTitle,
                $msgFail
            );
        }
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}
