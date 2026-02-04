<?php

namespace App\Repositories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Builder;

class CartRepository
{
    public function modelQuery(): Builder| Cart{
        return Cart::query();
    }

    public function getCart(int $userId): Cart{
        return Cart::query()
            ->with(['cartItems'])
            ->where('user_id',$userId)
            ->firstOrFail();
    }
}
