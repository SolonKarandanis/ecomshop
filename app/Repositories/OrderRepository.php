<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;

class OrderRepository
{

    public function modelQuery(): Builder| Order{
        return Order::query();
    }

    public function itemModelQuery(): Builder| OrderItem{
        return OrderItem::query();
    }
}
