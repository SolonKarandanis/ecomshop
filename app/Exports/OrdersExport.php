<?php

namespace App\Exports;

use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrdersExport implements FromCollection
{
    public function __construct(private readonly OrderRepository $orderRepository){}
    /**
    * @return Collection
    */
    public function collection(): Collection
    {
        return $this->orderRepository->getOrders();
    }
}
