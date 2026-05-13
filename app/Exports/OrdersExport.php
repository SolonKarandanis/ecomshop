<?php

namespace App\Exports;

use App\Dtos\OrderSearchRequestDTO;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrdersExport implements FromCollection
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderSearchRequestDTO $dto
    ){}
    /**
    * @return Collection
    */
    public function collection(): Collection
    {
        // getUsersOrders returns a paginator or array, so we need to bypass pagination for export
        // Let's get the base query and get() the results.
        return $this->orderRepository->getUsersOrdersForExport($this->dto)->get();
    }
}
