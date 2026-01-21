<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class UserRepository
{

    public function modelQuery(): Builder| User{
        return User::query();
    }

    public function getUsersWithOrderedItems(): Collection{
        return $this->modelQuery()
            ->select('users.name')
            ->selectRaw('GROUP_CONCAT(products.name SEPARATOR ",") as items')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->groupBy('users.name')
            ->get();
    }
}
