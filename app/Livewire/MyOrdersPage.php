<?php

namespace App\Livewire;

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
    public function render()
    {
        $result = $this->orderService->getUsersOrders(auth()->user()->id,10);
        return view('livewire.my-orders-page',['orders'=>$result]);
    }
}
