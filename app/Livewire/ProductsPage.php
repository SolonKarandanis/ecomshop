<?php

namespace App\Livewire;

use App\Dtos\ProductSearchFilterDto;
use App\Repositories\ProductRepository;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
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

    protected ProductRepository $productRepository;

    #[On('categoriesUpdated')]
    public function updateCategories($categories)
    {
        $this->selected_categories = $categories;
        $this->resetPage();
    }

    #[On('brandsUpdated')]
    public function updateBrands($brands)
    {
        $this->selected_brands = $brands;
        $this->resetPage();
    }

    public function boot(
        ProductRepository $productRepository
    ): void{
        $this->productRepository = $productRepository;
    }
    public function render()
    {
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
            'products'=>$searchResult,
        ]);
    }
}
