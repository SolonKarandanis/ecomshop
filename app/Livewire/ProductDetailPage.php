<?php

namespace App\Livewire;

use App\Repositories\ProductRepository;
use Livewire\Component;

class ProductDetailPage extends Component
{
    public $slug;

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
        return view('livewire.product-detail-page',compact('product'));
    }
}
