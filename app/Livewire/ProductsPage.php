<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title("Products Page - EcomShop")]
class ProductsPage extends Component
{
    use WithPagination;
    public function render()
    {
        $categories = Category::query()->where('is_active', 1)->get();
        $brands = Brand::query()->where('is_active', 1)->get();
        $productQuery = Product::query()->where('is_active', 1);
        return view('livewire.products-page',[
            'categories' => $categories,
            'brands' => $brands,
            'products'=>$productQuery->paginate(6),
        ]);
    }
}
