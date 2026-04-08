<?php

namespace App\Repositories;

use App\Dtos\CreateOrderDTO;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
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
                'address',
                'items',
                'items.product',
                'items.product.productAttributeValues.attribute',
                'items.product.productAttributeValues.media',
            ])
            ->find($orderId);
    }

    public function getLatestOrder(int $userId): Order{
        return $this->modelQuery()
            ->with([
                'address',
                'paymentMethod',
                'items',
                'items.product',
                'items.product.productAttributeValues.attribute',
                'items.product.productAttributeValues.media',
            ])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getUsersOrders(int $userId,?int $perPage): LengthAwarePaginator|array{
        return $this->modelQuery()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage??10);
    }

    public function updateOrder(Order $order): bool
    {
        return $order->save();
    }
}
