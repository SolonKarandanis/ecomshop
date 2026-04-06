<?php

namespace App\Repositories;

use App\Models\StripeOrderDetail;
use Illuminate\Database\Eloquent\Builder;

class StripeOrderDetailRepository
{

    public function modelQuery(): Builder| StripeOrderDetail{
        return StripeOrderDetail::query();
    }
}
