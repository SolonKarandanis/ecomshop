<?php

namespace App\Livewire;

use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Services\OrderService;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Orders')]
class MyOrdersPage extends Component
{
    use WithPagination;
    protected OrderService $orderService;

    public function boot(
        OrderService $orderService,
    ): void{
        $this->orderService = $orderService;
    }
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

    public function render()
    {
        $result = $this->orderService->getUsersOrders(auth()->user()->id,10);
        return view('livewire.my-orders-page',['orders'=>$result]);
    }
}
