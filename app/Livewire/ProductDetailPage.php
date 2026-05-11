<?php

namespace App\Livewire;

use App\Livewire\Traits\WithCartActions;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\UiService;
use Livewire\Component;

class ProductDetailPage extends Component
{
    use WithCartActions;
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
