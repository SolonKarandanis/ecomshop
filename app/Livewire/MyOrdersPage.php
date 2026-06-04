<?php

namespace App\Livewire;

use App\Dtos\OrderSearchRequestDTO;
use App\Enums\MessageSeverityEnum;
use App\Http\Requests\OrderSearchRequest;
use App\Services\OrderService;
use App\Services\UiService;
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
    public string $sortColumn = 'created_at';
    public string $sortDirection = 'desc';

    private const array SORTABLE_COLUMNS = ['id', 'created_at', 'order_status', 'payment_status', 'grand_total'];

    protected OrderService $orderService;
    protected UiService $uiService;



    public function boot(
        OrderService $orderService,
        UiService $uiService,
    ): void{
        $this->orderService = $orderService;
        $this->uiService = $uiService;
    }

    public function render()
    {
        $dto = $this->validateAndReturnDto();
        $result = $this->orderService->getUsersOrders($dto);
        return view('livewire.my-orders-page',['orders'=>$result]);
    }

    public function sort(string $column): void
    {
        if (!in_array($column, self::SORTABLE_COLUMNS, true)) {
            return;
        }
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    private function validateAndReturnDto(): OrderSearchRequestDTO
    {
        $request = new OrderSearchRequest();
        $request->merge([
            'orderStatus'   => $this->orderStatus,
            'paymentStatus' => $this->paymentStatus,
            'fromDate'      => $this->fromDate,
            'toDate'        => $this->toDate,
            'minPrice'      => $this->minPrice,
            'maxPrice'      => $this->maxPrice,
        ]);
        $this->validate($request->rules());
        return OrderSearchRequestDTO::fromRequest($request)
            ->withSortColumn($this->sortColumn)
            ->withSortDirection($this->sortDirection);
    }

    public function search(): void
    {
        $request = new OrderSearchRequest();
        $request->merge([
            'orderStatus'   => $this->orderStatus,
            'paymentStatus' => $this->paymentStatus,
            'fromDate'      => $this->fromDate,
            'toDate'        => $this->toDate,
            'minPrice'      => $this->minPrice,
            'maxPrice'      => $this->maxPrice,
        ]);
        $this->validate($request->rules());
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
    public function exportOrders()
    {
        $dto = $this->validateAndReturnDto();
        $countOrders = $this->orderService->countOrders($dto);

        if ($countOrders > 10000) {
            $this->uiService->showMessage(
                MessageSeverityEnum::ERROR,
                __('messages.export_orders.title'),
                __('messages.export_orders.limit_error', ['count' => number_format($countOrders)])
            );
            return;
        }

        return $this->orderService->exportOrders($dto);
    }
}
