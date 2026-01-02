<?php

namespace App\Repositories;

use App\Dtos\ProductSearchFilterDto;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{

    public function modelQuery(): Builder| Product{
        return Product::query();
    }

    public function getProductBySlug($slug){
        return $this->modelQuery()->where('slug', '=', $slug)->first();
    }

    public function searchProducts(ProductSearchFilterDto $dto): LengthAwarePaginator|array
    {
        $productQuery = $this->modelQuery()
            ->where('is_active', true)
            ->whereBetween('price', [$dto->getPriceFrom(), $dto->getPriceTo()]);
        if(!empty($dto->getSelectedCategories())){
            $productQuery->whereIn('category_id', $dto->getSelectedCategories());
        }
        if(!empty($dto->getSelectedBrands())){
            $productQuery->whereIn('brand_id', $dto->getSelectedBrands());
        }
        if($dto->isFeatured()){
            $productQuery->where('is_featured', true);
        }
        if($dto->isOnSale()){
            $productQuery->where('on_sale', true);
        }
        return $productQuery->paginate(6);
    }
}
