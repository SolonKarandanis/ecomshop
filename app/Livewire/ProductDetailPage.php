<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductDetailPage extends Component
{
    public $slug;

    public function mount($slug): void
    {
        $this->slug = $slug;
    }
    public function render()
    {
        $product = Product::query()->where('slug', '=', $this->slug)->first();
//        dd($product);
        return view('livewire.product-detail-page',compact('product'));
    }
}
