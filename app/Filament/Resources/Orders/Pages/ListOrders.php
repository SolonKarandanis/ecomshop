<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Widgets\OrderBuyerStatsWidget;
use App\Filament\Resources\Orders\Widgets\OrderStatsWidget;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array{
        return [
            OrderStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
          OrderBuyerStatsWidget::class,
        ];
    }

    public function getTabs(): array{
        return [
            null=>Tab::make('All'),
            'new'=>Tab::make('New')->query(fn($query)=>$query->where('order_status', 'new')),
            'processing'=>Tab::make('Processing')->query(fn($query)=>$query->where('order_status', 'processing')),
            'shipped'=>Tab::make('Shipped')->query(fn($query)=>$query->where('order_status', 'shipped')),
            'delivered'=>Tab::make('Delivered')->query(fn($query)=>$query->where('order_status', 'delivered')),
            'cancelled'=>Tab::make('Cancelled')->query(fn($query)=>$query->where('order_status', 'cancelled')),
        ];
    }
}
