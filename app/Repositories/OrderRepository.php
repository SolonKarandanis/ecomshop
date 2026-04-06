<?php

namespace App\Repositories;

use App\Dtos\CreateOrderDTO;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderRepository
{

    public function modelQuery(): Builder| Order{
        return Order::query();
    }

    public function itemModelQuery(): Builder| OrderItem{
        return OrderItem::query();
    }

    /**
     * @throws \Throwable
     */
    public function createOrder(CreateOrderDTO $createOrderDTO): Order{
        return DB::transaction(function () use ($createOrderDTO){
            $order = $this->modelQuery()->create([
                'user_id' => $createOrderDTO->getUserId(),
                'grand_total' => $createOrderDTO->getTotalPrice(),
                'payment_method_id' => $createOrderDTO->getPaymentMethodId(),
                'payment_status' => $createOrderDTO->getPaymentStatus(),
                'order_status' => $createOrderDTO->getOrderStatus(),
                'currency' => $createOrderDTO->getCurrency(),
                'shipping_method' => $createOrderDTO->getShippingMethod(),
                'shipping_amount' => $createOrderDTO->getShippingAmount(),
                'notes' => $createOrderDTO->getNotes(),
            ]);

            $order->items()->createMany($createOrderDTO->getOrderItems());

            return $order;
        });
    }

    public function getOrderById(int $orderId): Order{
        return $this->modelQuery()
            ->with([
                'items',
                'items.product',
                'items.product.productAttributeValues.attribute',
                'items.product.productAttributeValues.media',
            ])
            ->find($orderId);
    }
}
