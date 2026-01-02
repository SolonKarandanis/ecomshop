<?php

namespace App\Livewire;

use App\Models\Product;
use App\Repositories\BrandRepository;
use App\Repositories\CategoryRepository;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title("Products Page - EcomShop")]
class ProductsPage extends Component
{
    use WithPagination;

    #[Url('categories')]
    public $selected_categories=[];

    #[Url('brands')]
    public $selected_brands=[];

    #[Url('featured')]
    public $featured;

    #[Url('on_sale')]
    public $on_sale;

    #[Url('price_range')]
    public $price_range=3000;

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
        $productQuery = Product::query()
            ->where('is_active', 1)
            ->where('price','<=',$this->price_range);
        if(!empty($this->selected_categories)){
            $productQuery->whereIn('category_id', $this->selected_categories);
        }
        if(!empty($this->selected_brands)){
            $productQuery->whereIn('brand_id', $this->selected_brands);
        }
        if($this->featured){
            $productQuery->where('is_featured', true);
        }
        if($this->on_sale){
            $productQuery->where('on_sale', true);
        }
        return view('livewire.products-page',[
            'categories' => $categories,
            'brands' => $brands,
            'products'=>$productQuery->paginate(6),
        ]);
    }
}
