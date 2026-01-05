<?php

namespace App\Livewire;

use App\Dtos\ProductSearchFilterDto;
use App\Repositories\BrandRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
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

    #[Url('price_from')]
    public $price_from=0;

    #[Url('price_to')]
    public $price_to=3000;

    #[Url('sort')]
    public $sort='latest';

    protected CategoryRepository $categoryRepository;
    protected BrandRepository $brandRepository;
    protected ProductRepository $productRepository;

    public function boot(
        CategoryRepository $categoryRepository,
        BrandRepository $brandRepository,
        ProductRepository $productRepository
    ): void{
        $this->categoryRepository = $categoryRepository;
        $this->brandRepository = $brandRepository;
        $this->productRepository = $productRepository;
    }
    public function render()
    {
        $categories = $this->categoryRepository->getActiveCategories();
        $brands=$this->brandRepository->getActiveBrands();
        $productSearchFilterDto = new ProductSearchFilterDto();
        $productSearchFilterDto->setSelectedCategories($this->selected_categories);
        $productSearchFilterDto->setSelectedBrands($this->selected_brands);
        $productSearchFilterDto->setFeatured($this->featured??false);
        $productSearchFilterDto->setOnSale($this->on_sale??false);
        $productSearchFilterDto->setPriceFrom($this->price_from);
        $productSearchFilterDto->setPriceTo($this->price_to);
        $productSearchFilterDto->setSort($this->sort);
        $searchResult = $this->productRepository->searchProducts($productSearchFilterDto);
        return view('livewire.products-page',[
            'categories' => $categories,
            'brands' => $brands,
            'products'=>$searchResult,
        ]);
    }
}
