<?php

namespace App\Livewire;

use App\Dtos\AddToCartDto;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
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

    public function addToCart(int $productId, int $quantity, array $attributes): void{
        $product = $this->productRepository->getProductById($productId);
        $addToCartDto = AddToCartDto::withAttributes(
            $product->id,
            $quantity,
            $product->price,
            $attributes
        );
        $this->cartService->addItemsToCart([$addToCartDto]);
        $this->dispatch('cartUpdated');
        LivewireAlert::title('Add To Cart')
            ->text('Product added to cart successfully!')
            ->success()
            ->timer(2000)
            ->toast()
            ->position(Position::TopEnd)
            ->show();
    }

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
