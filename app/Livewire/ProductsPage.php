<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\BrandRepository;
use App\Repositories\CategoryRepository;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title("Products Page - EcomShop")]
class ProductsPage extends Component
{
    use WithPagination;

    protected CategoryRepository $categoryRepository;
    protected BrandRepository $brandRepository;

    public function boot(
        CategoryRepository $categoryRepository,
        BrandRepository $brandRepository,
    ): void{
        $this->categoryRepository = $categoryRepository;
        $this->brandRepository = $brandRepository;
    }
    public function render()
    {
        $categories = $this->categoryRepository->getActiveCategories();
        $brands=$this->brandRepository->getActiveBrands();
        $productQuery = Product::query()->where('is_active', 1);
        return view('livewire.products-page',[
            'categories' => $categories,
            'brands' => $brands,
            'products'=>$productQuery->paginate(6),
        ]);
    }
}
