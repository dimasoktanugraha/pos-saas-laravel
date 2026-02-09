<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Enums\StockMovementType;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\StockMovement;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter()
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->type->isIncoming()
                            ? "+{$state}"
                            : "-{$state}"
                    )
                    ->color(
                        fn($record) =>
                        $record->type->isIncoming()
                            ? 'success'
                            : 'danger'
                    ),

                TextColumn::make('stock_before')
                    ->label('Before')
                    ->alignCenter(),

                TextColumn::make('stock_after')
                    ->label('After')
                    ->alignCenter(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->tooltip(fn($state) => $state),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
                SelectFilter::make('type')
                    ->options(
                        collect(StockMovementType::cases())
                            ->mapWithKeys(fn($type) => [$type->value => $type->getLabel()])
                    )
                    ->multiple(),
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Product'),
                Filter::make('incoming')
                    ->label('Stock Incoming')
                    ->query(fn(Builder $query) => $query->incoming()),

                Filter::make('outgoing')
                    ->label('Stoct Outgoing')
                    ->query(fn(Builder $query) => $query->outgoing()),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date'),
                        DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Dari: ' . Carbon::parse($data['from'])->format('d M Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Sampai: ' . Carbon::parse($data['until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
