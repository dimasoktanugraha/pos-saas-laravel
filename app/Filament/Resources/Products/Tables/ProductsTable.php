<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\View;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use App\Models\Product;
use Filament\Actions\Action;
use App\Models\StockMovement;
use App\Enums\StockMovementType;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->color(
                        fn(Product $record) =>
                        $record->isOutOfStock()
                            ? 'danger'
                            : ($record->isLowStock() ? 'warning' : 'success')
                    )
                    ->icon(
                        fn(Product $record) =>
                        $record->isLowStock()
                            ? 'heroicon-o-exclamation-triangle'
                            : null
                    ),
                TextColumn::make('min_stock')
                    ->label('Min')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                ImageColumn::make('image')
                    ->label('Product Image')
                    ->disk('public')
                    ->circular()
                    ->size(50),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('category_id')
                    ->label('Category')
                    ->getStateUsing(fn($record) => $record->category ? $record->category->name : null)
                    ->searchable()
                    ->sortable(),
                // TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ViewAction::make(),
                // Stock Adjustment Action
                Action::make('adjustStock')
                    ->label('Adjust Stock')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        Select::make('type')
                            ->label('Jenis Adjustment')
                            ->options([
                                StockMovementType::AdjustmentIn->value => 'Tambah Stok (+)',
                                StockMovementType::AdjustmentOut->value => 'Kurangi Stok (-)',
                                StockMovementType::Damaged->value => 'Barang Rusak',
                                StockMovementType::Expired->value => 'Barang Kadaluarsa',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (Product $record, array $data) {
                        $type = StockMovementType::from($data['type']);
                        $quantity = (int) $data['quantity'];
                        $stockBefore = $record->stock;

                        // Calculate new stock
                        if ($type->isIncoming()) {
                            $stockAfter = $stockBefore + $quantity;
                        } else {
                            $stockAfter = max(0, $stockBefore - $quantity);
                        }

                        // Create stock movement record
                        StockMovement::create([
                            'product_id' => $record->id,
                            'type' => $type->value,
                            'quantity' => $quantity,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'notes' => $data['notes'],
                            'user_id' => auth()->id(),
                        ]);

                        // Update product stock
                        $record->update(['stock' => $stockAfter]);

                        Notification::make()
                            ->success()
                            ->title('Stock Updated')
                            ->body("Stock berhasil diubah dari {$stockBefore} menjadi {$stockAfter}")
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
