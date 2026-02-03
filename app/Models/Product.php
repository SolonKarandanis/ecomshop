<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $category_id
 * @property int $brand_id
 * @property string $name
 * @property string $slug
 * @property string|null $images
 * @property string|null $description
 * @property numeric $price
 * @property int $is_active
 * @property int $is_featured
 * @property int $in_stock
 * @property int $on_sale
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereInStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereOnSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @property-read \App\Models\Brand $brand
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \App\Models\ProductAttributeValues|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attribute> $attributes
 * @property-read int|null $attributes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductAttributeValues> $productAttributeValues
 * @property-read int|null $product_attribute_values_count
 * @mixin \Eloquent
 */
class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable=[
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'price',
        'is_active',
        'is_featured',
        'in_stock',
        'on_sale',
    ];

    protected $casts=[];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100);
        $this->addMediaConversion('small')
            ->width(480);
        $this->addMediaConversion('large')
            ->width(1200);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo{
        return $this->belongsTo(Brand::class);
    }

    public function orderItems(): HasMany{
        return $this->hasMany(OrderItem::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute_values', 'product_id', 'attribute_id')
            ->using(ProductAttributeValues::class);
    }

    public function productAttributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValues::class);
    }

    public function colorAttributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValues::class)
            ->whereHas('attribute', function ($query) {
                $query->where('name', 'attribute.color');
            });
    }

    public function panelTypeAttributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValues::class)
            ->whereHas('attribute', function ($query) {
                $query->where('name', 'attribute.panel.type');
            });
    }

    protected function getColorProductAttributeValue(): ?ProductAttributeValues
    {
        if ($this->relationLoaded('colorAttributeValues')) {
            return $this->colorAttributeValues->first();
        }
        // This fallback will still work, but it will be less efficient if the relation isn't preloaded.
        return $this->colorAttributeValues()->first();
    }

    protected function checkIfProductAttributeValueExists(ProductAttributeValues|null $productAttributeValue):bool{
        return $productAttributeValue && $productAttributeValue->hasMedia('product-attribute-images');
    }

    public function getThumbnailImage():string
    {
        $productAttributeValue = $this->getColorProductAttributeValue();
        if ($this->checkIfProductAttributeValueExists($productAttributeValue)) {
            return $productAttributeValue->getFirstMediaUrl('product-attribute-images', 'thumb');
        }

        return '';
    }

    public function getSmallImage():string
    {
        $productAttributeValue = $this->getColorProductAttributeValue();
        if ($this->checkIfProductAttributeValueExists($productAttributeValue)) {
            return $productAttributeValue->getFirstMediaUrl('product-attribute-images', 'small');
        }

        return '';
    }

    public function getLargeImage():string
    {
        $productAttributeValue = $this->getColorProductAttributeValue();
        if ($this->checkIfProductAttributeValueExists($productAttributeValue)) {
            return $productAttributeValue->getFirstMediaUrl('product-attribute-images', 'large');
        }

        return '';
    }
}
