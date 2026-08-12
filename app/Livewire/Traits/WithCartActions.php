<?php

namespace App\Livewire\Traits;

use App\Attributes\PreAuthorize;
use App\Dtos\AddToCartDto;
use App\Exceptions\CartException;
use App\Exceptions\ProductNotFoundException;

trait WithCartActions
{
    use WithMessages, WithPreAuthorize;

    /**
     * @throws ProductNotFoundException
     * @throws CartException
     */
    #[PreAuthorize('buyer-action')]
    public function addToCart(int $productId, int $quantity = 1, array $attributes = []): void
    {
        if (! $this->isPreAuthorized(__FUNCTION__)) {
            $this->uiService->addToCartError();

            return;
        }

        $product = $this->productService->getProductById($productId);
        $addToCartDto = AddToCartDto::withAttributes(
            $product->id,
            $quantity,
            $product->price,
            $attributes
        );
        $title = __('messages.add_to_cart.title');
        $success = __('messages.add_to_cart.success');
        $error = __('messages.add_to_cart.error');
        try {
            $this->cartService->addItemsToCart([$addToCartDto]);
            $this->handleSuccess('cartUpdated', $title, $success);
        } catch (CartException $e) {
            $this->handleError($title, $e->getMessage(), $e);
        } catch (ProductNotFoundException $e) {
            $this->handleError($title, $error, $e);
        }
    }
}
