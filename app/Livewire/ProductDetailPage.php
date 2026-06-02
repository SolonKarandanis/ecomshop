<?php

namespace App\Livewire;

use App\Livewire\Traits\WithCartActions;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\UiService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProductDetailPage extends Component
{
    use WithCartActions;
    public string $slug;

    public bool $hasColorAttribute = false;
    public bool $hasPanelTypeAttribute = false;
    public bool $hasHardDriveAttribute = false;
    public bool $hasKeyboardAttribute = false;
    public bool $hasRamAttribute = false;
    public bool $hasGpuAttribute = false;

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

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    #[Computed]
    public function product(): Product
    {
        return $this->productRepository->getProductBySlug($this->slug);
    }

    public function render()
    {
        $product = $this->product;
        $this->hasColorAttribute      = $product->colorAttributeValues->count() > 0;
        $this->hasPanelTypeAttribute  = $product->panelTypeAttributeValues->count() > 0;
        $this->hasHardDriveAttribute  = $product->hardDriveAttributeValues->count() > 0;
        $this->hasKeyboardAttribute   = $product->keyboardAttributeValues->count() > 0;
        $this->hasRamAttribute        = $product->ramAttributeValues->count() > 0;
        $this->hasGpuAttribute        = $product->gpuAttributeValues->count() > 0;
        return view('livewire.product-detail-page', compact('product'));
    }
}
