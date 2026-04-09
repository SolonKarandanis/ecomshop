<?php

namespace App\Traits;

use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderStatusEnum;

trait HasStatusClasses
{
    public function getOrderStatusClass($status): string
    {
        return match ($status) {
            OrderStatusEnum::Draft->value => 'bg-slate-500',
            OrderStatusEnum::Paid->value => 'bg-blue-500',
            OrderStatusEnum::Shipped->value => 'bg-orange-500',
            OrderStatusEnum::Delivered->value => 'bg-green-500',
            OrderStatusEnum::Cancelled->value => 'bg-red-500',
            default => 'bg-slate-500',
        };
    }

    public function getPaymentStatusClass($status): string
    {
        return match ($status) {
            OrderPaymentStatusEnum::PAID->value => 'bg-green-500',
            OrderPaymentStatusEnum::PENDING->value => 'bg-blue-500',
            OrderPaymentStatusEnum::FAILED->value => 'bg-red-500',
            default => 'bg-slate-500',
        };
    }
}
