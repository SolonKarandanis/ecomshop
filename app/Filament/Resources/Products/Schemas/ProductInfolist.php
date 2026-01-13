<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category.name')
                    ->label('Category'),
                TextEntry::make('brand.name')
                    ->label('Brand'),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                ImageEntry::make('images')
                    ->disk('public')
                    ->placeholder('-')
                    ->getStateUsing(function ($record) {
                        $productAttributeValue = $record->productAttributeValues()
                            ->whereHas('attribute', function ($query) {
                                $query->where('name', 'attribute.color');
                            })
                            ->first();

                        if ($productAttributeValue && $productAttributeValue->hasMedia('product-attribute-images')) {

                            return $productAttributeValue->getFirstMediaUrl('product-attribute-images','thumb');
                        }
                        return null;
                    }),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('price')
                    ->money('$'),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('is_featured')
                    ->boolean(),
                IconEntry::make('in_stock')
                    ->boolean(),
                IconEntry::make('on_sale')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
