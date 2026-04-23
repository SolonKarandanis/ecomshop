<?php

namespace App\Livewire;

use App\Dtos\AddToCartDto;
use App\Enums\MessageSeverityEnum;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\UiService;
use Livewire\Component;

class ProductDetailPage extends Component
{
    public $slug;

    public bool $hasColorAttribute;
    public bool $hasPanelTypeAttribute;
    public bool $hasHardDriveAttribute;
    public bool $hasKeyboardAttribute;
    public bool $hasRamAttribute;
    public bool $hasGpuAttribute;

    protected ProductRepository $productRepository;
    protected CartService $cartService;

    protected UiService $uiService;

    /**
     * @throws \Throwable
     */
    public function addToCart(int $productId, int $quantity, array $attributes): void{
        $product = $this->productRepository->getProductById($productId);
        $addToCartDto = AddToCartDto::withAttributes(
            $product->id,
            $quantity,
            $product->price,
            $attributes
        );
        $result= $this->cartService->addItemsToCart([$addToCartDto]);
        $this->handleActionResult($result);
    }

    protected function handleActionResult(bool $result):void
    {
        if($result){
            $this->dispatch('cartUpdated');
            $this->uiService->showMessage(
                MessageSeverityEnum::SUCCESS,
                __('messages.add_to_cart.title'),
                __('messages.add_to_cart.success')
            );
        }
        else{
            $this->uiService->showMessage(
                MessageSeverityEnum::ERROR,
                __('messages.add_to_cart.title'),
                __('messages.add_to_cart.error')
            );
        }
    }

    public function boot(
        ProductRepository $productRepository,
        CartService $cartService,
        UiService $uiService
    ): void{
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
        $this->uiService = $uiService;
    }

    public function mount($slug): void
    {
        $this->slug = $slug;
    }
    public function render()
    {
        $product = $this->productRepository->getProductBySlug($this->slug);
        $this->hasColorAttribute = $product->colorAttributeValues->count() > 0;
        $this->hasPanelTypeAttribute = $product->panelTypeAttributeValues->count() > 0;
        $this->hasHardDriveAttribute = $product->hardDriveAttributeValues->count() > 0;
        $this->hasKeyboardAttribute = $product->keyboardAttributeValues->count() > 0;
        $this->hasRamAttribute = $product->ramAttributeValues->count() > 0;
        $this->hasGpuAttribute = $product->gpuAttributeValues->count() > 0;
        return view('livewire.product-detail-page',compact('product'));
    }
}
