<?php

namespace App\Livewire;

use App\Repositories\ProductRepository;
use App\Services\CartService;
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

    public function boot(
        ProductRepository $productRepository,
        CartService $cartService
    ): void{
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
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
