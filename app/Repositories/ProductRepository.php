<?php

namespace App\Repositories;

use App\Dtos\ProductSearchFilterDto;
use App\Models\Product;
use App\Models\ProductAttributeValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductRepository
{

    public function modelQuery(): Builder| Product{
        return Product::query();
    }

    public function getProductById(int $id): Product{
        return $this->modelQuery()
            ->with([
                'productAttributeValues.attribute',
                'productAttributeValues.attributeOption',
                'productAttributeValues.media',
            ])
            ->where('id', '=', $id)->firstOrFail();
    }

    public function getProductBySlug($slug): Product{
        return $this->modelQuery()
            ->with([
                'productAttributeValues.attribute',
                'productAttributeValues.attributeOption',
                'productAttributeValues.media',
            ])
            ->where('slug', '=', $slug)->firstOrFail();
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

    /**
     * @param int[] $productIds
     */
    public function findProductsByIdsWithDefaultAttributes(array $productIds): Collection{
        return $this->modelQuery()
            ->with([
                'attributes.attributeOptions' => function ($query) {
                    $query->orderBy('id')->limit(1);
                }
            ])
            ->whereIn('id', $productIds)
            ->get();
    }

    /**
     * @param int[] $productIds
     */
    public function findProductsByIds(array $productIds): Collection{
        return $this->modelQuery()
            ->with([
                'attributes.attributeOptions',
                'productAttributeValues.attribute',
                'productAttributeValues.attributeOption',
            ])
            ->whereIn('id', $productIds)
            ->distinct()
            ->get();
    }

    public function findProductsForCart(array $productOptions): Collection
    {
        $productIds = array_keys($productOptions);
        if (empty($productIds)) {
            return collect();
        }

        $pavQuery = ProductAttributeValues::query()->with(['attribute', 'attributeOption']);

        foreach ($productOptions as $productId => $optionIds) {
            if (!empty($optionIds)) {
                $pavQuery->orWhere(function ($query) use ($productId, $optionIds) {
                    $query->where('product_id', $productId)
                        ->whereIn('attribute_option_id', $optionIds);
                });
            }
        }

        $pvs = $pavQuery->get();
        $pvsByProduct = $pvs->groupBy('product_id');

        $products = $this->modelQuery()->whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($products as $product) {
            $productPvs = $pvsByProduct->get($product->id, collect());
            $product->setRelation('productAttributeValues', $productPvs);

            $attributes = collect();
            foreach ($productPvs as $pv) {
                if (!$pv->relationLoaded('attribute') || !$pv->relationLoaded('attributeOption')) {
                    continue;
                }

                $attribute = $pv->attribute;
                if (!$attributes->has($attribute->id)) {
                    $attribute->setRelation('attributeOptions', collect());
                    $attributes->put($attribute->id, $attribute);
                }
                $attributes->get($attribute->id)->attributeOptions->push($pv->attributeOption);
            }

            foreach ($attributes as $attribute) {
                $attribute->setRelation('attributeOptions', $attribute->attributeOptions->unique('id')->values());
            }

            $product->setRelation('attributes', $attributes->values());
        }

        return $products;
    }
}
