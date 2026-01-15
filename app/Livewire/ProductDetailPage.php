<?php

namespace App\Livewire;

use App\Repositories\ProductRepository;
use Livewire\Component;

class ProductDetailPage extends Component
{
    public $slug;

    public bool $hasColorAttribute;

    public bool $hasPanelTypeAttribute;

    protected ProductRepository $productRepository;

    public function boot():void{
        $this->productRepository = new ProductRepository();
    }

    public function mount($slug): void
    {
        $this->slug = $slug;
    }
    public function render()
    {
        $product = $this->productRepository->getProductBySlug($this->slug);
        $this->hasColorAttribute = $product->getAttributeValues('attribute.color')->count() > 0;
        $this->hasPanelTypeAttribute = $product->getAttributeValues('attribute.panel.type')->count() > 0;
        return view('livewire.product-detail-page',compact('product'));
    }
}
