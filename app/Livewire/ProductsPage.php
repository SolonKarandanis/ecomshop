<?php

namespace App\Livewire;

use App\Dtos\ProductSearchFilterDto;
use App\Dtos\AddToCartDto;
use App\Enums\MessageSeverityEnum;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\UiService;
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

    protected UiService $uiService;

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

    /**
     * @throws \Throwable
     */
    public function addToCart(int $productId): void
    {
        $product = $this->productRepository->getProductById($productId);
        $addToCartDto = new AddToCartDto(
            $product->id,
            1,
            $product->price
        );
        $result= $this->cartService->addItemsToCart([$addToCartDto]);
        $this->handleActionResult($result);
    }

    protected function handleActionResult(bool $result):void
    {
        if($result){
            $this->dispatch('cartUpdated');
            $this->uiService->showMessage(MessageSeverityEnum::SUCCESS,'Add To Cart','Product added to cart successfully!');
        }
        else{
            $this->uiService->showMessage(MessageSeverityEnum::ERROR,'Add To Cart','Failed to add product to cart!');
        }
    }

    public function boot(
        ProductRepository $productRepository,
        CartService $cartService,
        UiService $uiService
    ): void{
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
        $this->uiService = $uiService;
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
