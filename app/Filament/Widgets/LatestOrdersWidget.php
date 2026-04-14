<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrdersWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort=2;

    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at','desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Order Id')
                    ->sortable(true),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable()
                    ->money('eur'),
                TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_status')
                    ->badge()
                    ->color(fn(string $state):string=> match($state){
                        'order.status.draft' => 'info',
                        'order.status.paid' => 'info',
                        'order.status.shipped' => 'warning',
                        'order.status.delivered' => 'success',
                        'order.status.cancelled' => 'danger',
                    })
                    ->icon(fn(string $state):string=>match($state){
                        'order.status.draft' => 'heroicon-m-sparkles',
                        'order.status.paid' => 'heroicon-m-arrow-path',
                        'order.status.shipped' => 'heroicon-m-truck',
                        'order.status.delivered' => 'heroicon-m-check-badge',
                        'order.status.cancelled' => 'heroicon-m-x-circle',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shipping_method')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('View Order')
                ->url(fn(Order $record):string=>OrderResource::getUrl('view',['record'=>$record->id]))
                ->icon('heroicon-m-eye')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
