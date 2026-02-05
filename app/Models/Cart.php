<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $cartItems
 * @property-read int|null $cart_items_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart query()
 * @mixin \Eloquent
 */
class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';

    protected $fillable=[
        'user_id',
        'total_price'
    ];

    public function recalculateCartTotalPrice(): void
    {
        $total = 0;
        foreach ($this->cartItems as $item) {
            $total += $item->total_price;
        }
        $this->total_price = $total;
    }

    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function cartItems():HasMany{
        return $this->hasMany(CartItem::class);
    }
}
