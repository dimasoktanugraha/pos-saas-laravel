<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Enums\StockMovementType;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $product = Product::find($data['product_id']);
        $type = StockMovementType::from($data['type']);

        // Ambil stock saat ini dari product (bukan dari form)
        $stockBefore = $product->stock;
        $quantity = (int) $data['quantity'];

        // Calculate stock_after berdasarkan type
        if ($type->isIncoming()) {
            $stockAfter = $stockBefore + $quantity;
        } else {
            $stockAfter = $stockBefore - $quantity;
            // Validasi: stock tidak boleh negatif
            if ($stockAfter < 0) {
                $stockAfter = 0;
                $quantity = $stockBefore;
            }
        }

        // Set nilai yang akan disimpan ke database
        $data['stock_before'] = $stockBefore;
        $data['stock_after'] = $stockAfter;
        $data['user_id'] = Filament::auth()->id();

        // Update product stock dengan nilai baru
        $product->update(['stock' => $stockAfter]);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
