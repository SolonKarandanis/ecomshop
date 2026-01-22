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
        return $this->modelQuery()
            ->with([
                'productAttributeValues' => function ($query) {
                    $query->with(['attribute','attributeOption', 'media']);
                }
            ])
            ->where('slug', '=', $slug)->first();
    }

    public function searchProducts(ProductSearchFilterDto $dto): LengthAwarePaginator|array
    {
        $productQuery = $this->modelQuery()
            ->with([
                'productAttributeValues' => function ($query) {
                    $query->whereHas('attribute', function ($query) {
                        $query->where('name', 'attribute.color');
                    })->with(['media', 'attribute']);
                }
            ])
            ->where('is_active', true)
            ->whereBetween('price', [$dto->getPriceFrom(), $dto->getPriceTo()]);

        $productQuery->when(!empty($dto->getSelectedCategories()),function($query) use ($dto){
            $query->whereIn('category_id', $dto->getSelectedCategories());
        });

        $productQuery->when(!empty($dto->getSelectedBrands()),function($query) use ($dto){
            $query->whereIn('brand_id', $dto->getSelectedBrands());
        });

        $productQuery->when($dto->isFeatured(),function($query) use ($dto){
            $query->where('is_featured', true);
        });

        $productQuery->when($dto->isOnSale(),function($query) use ($dto){
            $query->where('on_sale', true);
        });

        $productQuery->when($dto->getSort()==='latest',function($query) use ($dto){
            $query->orderBy('created_at', 'desc');
        });

        $productQuery->when($dto->getSort()==='price',function($query) use ($dto){
            $query->orderBy('price', 'desc');
        });

        return $productQuery->paginate(6);
    }
}
