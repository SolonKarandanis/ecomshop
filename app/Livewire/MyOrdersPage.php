<?php

namespace App\Livewire;

use App\Http\Requests\OrderSearchRequest;
use App\Services\OrderService;
use App\Traits\HasStatusClasses;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Exception;

#[Title('My Orders')]
class MyOrdersPage extends Component
{
    use WithPagination, HasStatusClasses;

    public ?string $orderStatus = '';
    public ?string $paymentStatus = '';
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public ?float $minPrice = null;
    public ?float $maxPrice = null;

    protected OrderService $orderService;



    public function boot(
        OrderService $orderService,
    ): void{
        $this->orderService = $orderService;
    }

    public function render()
    {
        $validated = $this->validate((new OrderSearchRequest())->rules());
        $result = $this->orderService->getUsersOrders(auth()->user()->id,5);
        return view('livewire.my-orders-page',['orders'=>$result]);
    }

    public function search(): void
    {
        $validated = $this->validate((new OrderSearchRequest())->rules());
        $this->resetPage();
    }

    public function resetSearch(): void
    {
        $this->reset(['orderStatus', 'paymentStatus', 'fromDate', 'toDate', 'minPrice', 'maxPrice']);
        $this->resetPage();
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportOrders(): void
    {
        $this->orderService->exportOrders();
    }
}
