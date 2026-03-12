<?php

namespace App\Livewire;

use App\Dtos\ProductSearchFilterDto;
use App\Dtos\AddToCartDto;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
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
    protected CartService $cartService;

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

    public function addToCart(Product $product): void
    {
        $addToCartDto = new AddToCartDto(
            $product->id,
            1,
            $product->price
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
