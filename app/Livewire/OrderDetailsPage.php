<?php

namespace App\Livewire;

use App\Services\OrderService;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Order Details')]
class OrderDetailsPage extends Component
{
    public $id;
    protected OrderService $orderService;

    public function boot(
        OrderService $orderService,
    ): void{
        $this->orderService = $orderService;
    }

    public function mount($id): void
    {
        $this->id = $id;
    }

    public function render()
    {
        $order = $this->orderService->getOrderById($this->id);
        return view('livewire.order-details-page',['order'=>$order]);
    }
}
