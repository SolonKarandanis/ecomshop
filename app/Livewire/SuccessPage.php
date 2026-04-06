<?php

namespace App\Livewire;

use App\Services\OrderService;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Order Success')]
class SuccessPage extends Component
{
    protected OrderService $orderService;

    public function boot(
        OrderService $orderService,
    ): void{
        $this->orderService = $orderService;
    }
    public function render()
    {
        $userId = auth()->user()->id;
        $latestOrder = $this->orderService->getUsersLatestOrder($userId);
        return view('livewire.success-page',['order'=>$latestOrder]);
    }
}
