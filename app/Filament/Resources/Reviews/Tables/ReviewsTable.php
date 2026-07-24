<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Enums\ReviewStatusEnum;
use App\Models\Review;
use App\Services\ReviewService;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Buyer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rating')
                    ->sortable(),
                TextColumn::make('comment')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ReviewStatusEnum::labels()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ReviewStatusEnum::PUBLISHED->value => 'success',
                        ReviewStatusEnum::HIDDEN->value => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                    ]),
                SelectFilter::make('status')
                    ->options(ReviewStatusEnum::labels()),
                SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable(),
                SelectFilter::make('user')
                    ->label('Buyer')
                    ->relationship('user', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (Review $record) => $record->status === ReviewStatusEnum::HIDDEN->value)
                    ->action(fn (Review $record) => app(ReviewService::class)->updateReviewStatus($record, ReviewStatusEnum::PUBLISHED)),
                Action::make('hide')
                    ->label('Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Review $record) => $record->status === ReviewStatusEnum::PUBLISHED->value)
                    ->action(fn (Review $record) => app(ReviewService::class)->updateReviewStatus($record, ReviewStatusEnum::HIDDEN)),
            ]);
    }
}
