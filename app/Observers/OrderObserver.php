<?php

namespace App\Observers;

use App\Enums\NotificationEventTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Notifications\OrderNotification;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status')) {
            return;
        }

        $eventType = match ($order->order_status) {
            OrderStatusEnum::Shipped->value   => NotificationEventTypeEnum::ORDER_SHIPPED,
            OrderStatusEnum::Delivered->value => NotificationEventTypeEnum::ORDER_DELIVERED,
            OrderStatusEnum::Cancelled->value => NotificationEventTypeEnum::ORDER_CANCELLED,
            default                           => null,
        };

        if ($eventType === null) {
            return;
        }

        $message = match ($eventType) {
            NotificationEventTypeEnum::ORDER_SHIPPED   => "Your order #{$order->id} has been shipped.",
            NotificationEventTypeEnum::ORDER_DELIVERED => "Your order #{$order->id} has been delivered.",
            NotificationEventTypeEnum::ORDER_CANCELLED => "Your order #{$order->id} has been cancelled.",
        };

        $order->user->notify(new OrderNotification($eventType, $order->id, $message, $order->user->id));
    }
}
