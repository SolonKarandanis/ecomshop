<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Attribute;
use App\Models\AttributeOptions;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make('Product Information')->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->readOnly()
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('products/attachments')
                    ])->columns(2),
                    Section::make('Product Images')->schema([
                        FileUpload::make('images')
                            ->multiple()
                            ->disk('public')
                            ->directory('products/images')
                            ->maxFiles(5)
                            ->image()
                            ->reorderable()
                    ]),
                    Section::make('Product Variations')
                        ->schema([
                            Repeater::make('productAttributeValues')
                                ->relationship()
                                ->schema([
                                    Select::make('attribute_id')
                                        ->label('Attribute')
                                        ->options(Attribute::all()->pluck('name', 'id')->toArray())
                                        ->reactive()
                                        ->required(),
                                    Select::make('attribute_option_id')
                                        ->label('Value')
                                        ->options(function (Get $get) {
                                            $attribute = Attribute::find($get('attribute_id'));
                                            if ($attribute) {
                                                return $attribute->attributeOptions->pluck('name', 'id');
                                            }
                                            return AttributeOptions::all()->pluck('name', 'id');
                                        })
                                        ->required()
                                ])->columns(2)
                        ])
                ])->columnSpan(2),
                Group::make()->schema([
                    Section::make('Price')->schema([
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                    ]),
                    Section::make('Associations')->schema([
                        Select::make('category_id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('category', 'name'),
                        Select::make('brand_id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('brand', 'name')
                    ]),
                    Section::make('Status')->schema([
                        Toggle::make('in_stock')
                            ->required()
                            ->default(true),
                        Toggle::make('is_active')
                            ->required()
                            ->default(true),
                        Toggle::make('is_featured')
                            ->required()
                            ->default(false),
                    ])
                ])->columnSpan(1)
            ])->columns(3);
    }
}
