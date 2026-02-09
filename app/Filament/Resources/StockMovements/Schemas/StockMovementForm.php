<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Enums\StockMovementType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Models\Product;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stock Movement Detail')
                    ->components([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $product = Product::find($state);
                                $set('stock_before', $product ? $product->stock : 0);
                            }),
                        Select::make('type')
                            ->options(
                                collect(StockMovementType::adjustmentTypes())
                            ->mapWithKeys(fn($type) =>
                                [$type->value => $type->getLabel()])
                            )
                            ->required()
                            ->native(false)
                            ->live(),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->live(debounce: 500)
                            ->helperText('Input quantity (must be positive)'),
                        TextInput::make('stock_before')
                            ->label('Stock Before')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(true),
                        Placeholder::make('stock_after_preview')
                            ->label('Stock After (Preview)')
                            ->content(function (Get $get): string {
                                $stockBefore = (int) $get('stock_before');
                                $quantity = (int) $get('quantity');
                                $type = $get('type');

                                if (!$type || !$quantity) {
                                    return '-';
                                }

                                $movementType = StockMovementType::tryFrom($type);
                                if (!$movementType) {
                                    return '-';
                                }

                                if ($movementType->isIncoming()) {
                                    $stockAfter = $stockBefore + $quantity;
                                } else {
                                    $stockAfter = max(0, $stockBefore - $quantity);
                                }

                                return (string) $stockAfter;
                            }),
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
