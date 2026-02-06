<?php

namespace App\Repositories;

use App\Dtos\AddToCartDto;
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

    public function saveCart(Cart $cart): void
    {
        $cart->update($cart->getFillable());
    }

    public function findItemByProductIdAndAttributes(int $cartId,int $productId, array $attributes): CartItem| null{
        return $this->itemModelQuery()
            ->where('cart_id',$cartId)
            ->where('product_id',$productId)
            ->where('attributes',json_encode($attributes))
            ->first();
    }

    public function updateItemQuantity(int $cartItemId, int $quantity): void{
        $this->itemModelQuery()->where('id',$cartItemId)->update([
            'quantity' => DB::raw('quantity+'.$quantity),
        ]);
    }

    public function createCartItem(AddToCartDto $addToCartDto):void{
        $total_price = $addToCartDto->getQuantity() * $addToCartDto->getPrice();
        $this->itemModelQuery()->create([
            'product_id' => $addToCartDto->getProductId(),
            'quantity' => $addToCartDto->getQuantity(),
            'unit_price' => $addToCartDto->getPrice(),
            'total_price' => $total_price,
            'attributes' => json_encode($addToCartDto->getAttributes()),
        ]);
    }

    public function deleteCartItem(int $cartItemId):void{
        $this->itemModelQuery()
            ->where('id', $cartItemId)
            ->delete();
    }
}
