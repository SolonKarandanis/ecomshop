<?php

namespace App\Livewire\Traits;

use App\Attributes\PreAuthorize;
use App\Dtos\AddToCartDto;
use App\Enums\MessageSeverityEnum;
use App\Exceptions\CartException;
use App\Exceptions\ProductNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;

trait WithCartActions
{
    use WithPreAuthorize;

    /**
     * @throws ProductNotFoundException
     * @throws CartException
     */
    #[PreAuthorize('buyer-action')]
    public function addToCart(int $productId, int $quantity = 1, array $attributes = []): void
    {
        if (!$this->isPreAuthorized(__FUNCTION__)) {
            $this->uiService->addToCartError();
            return;
        }

        $product = $this->productRepository->getProductById($productId);
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
        } catch (CartException|ProductNotFoundException $e) {
            $this->handleError($title, $error, $e);
        }
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
}
