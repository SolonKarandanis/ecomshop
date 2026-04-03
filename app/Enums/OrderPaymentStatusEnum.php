<?php

namespace App\Enums;

enum OrderPaymentStatusEnum:string
{

    case PENDING = 'order.payment.status.pending';
    case PAID = 'order.payment.status.paid';

    public static function labels(): array
    {
        return [
            self::PENDING->value => __('order.payment.status.pending'),
            self::PAID->value => __('order.payment.status.paid'),
        ];
    }
}
