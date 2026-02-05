<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CartRepository
{
    public function modelQuery(): Builder| Cart{
        return Cart::query();
    }

    public function itemModelQuery(): Builder| CartItem{
        return CartItem::query();
    }

    public function getCart(int $userId): Cart{
        return $this->modelQuery()
            ->with(['cartItems'])
            ->where('user_id',$userId)
            ->firstOrFail();
    }

    public function findItemByProductIdAndAttributes(int $productId, array $attributes): CartItem| null{
        return $this->itemModelQuery()
            ->where('product_id',$productId)
            ->where('attributes',json_encode($attributes))
            ->first();
    }

    public function updateItemQuantity(int $cartItemId, int $quantity): void{
        $this->itemModelQuery()->where('id',$cartItemId)->update([
            'quantity' => DB::raw('quantity+'.$quantity),
        ]);
    }

    public function deleteCartItem(int $cartItemId):void{
        $this->modelQuery()
            ->where('id', $cartItemId)
            ->delete();
    }
}
