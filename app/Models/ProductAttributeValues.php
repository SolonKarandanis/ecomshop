<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read \App\Models\Attribute|null $attribute
 * @property-read \App\Models\AttributeOptions|null $attributeOption
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValues newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValues newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAttributeValues query()
 * @mixin \Eloquent
 */
class ProductAttributeValues extends Pivot
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    protected $table = 'product_attribute_values';

    protected $fillable=[
        'product_id',
        'attribute_id',
        'attribute_option_id',
        'attribute_value_method',
        'attribute_value'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function attribute(): BelongsTo{
        return $this->belongsTo(Attribute::class, 'attribute_id', 'id');
    }
    public function attributeOption(): BelongsTo{
        return $this->belongsTo(AttributeOptions::class, 'attribute_option_id', 'id');
    }
}
