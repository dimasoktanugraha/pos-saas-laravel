<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StockMovementType: string implements HasLabel, HasColor, HasIcon
{
    // Incoming (IN)
    case Purchase = 'purchase';
    case ReturnCustomer = 'return_customer';
    case AdjustmentIn = 'adjustment_in';
    case TransferIn = 'transfer_in';

    // Outgoing (OUT)
    case Sale = 'sale';
    case ReturnSupplier = 'return_supplier';
    case AdjustmentOut = 'adjustment_out';
    case TransferOut = 'transfer_out';
    case Damaged = 'damaged';
    case Expired = 'expired';

    public function getLabel(): ?string{
        return match ($this){
            self::Purchase => 'Pembelian',
            self::ReturnCustomer => 'Retur dari Pelanggan',
            self::AdjustmentIn => 'Penyesuaian Masuk',
            self::TransferIn => 'Transfer Masuk',

            self::Sale => 'Penjualan',
            self::ReturnSupplier => 'Retur dari Supplier',
            self::AdjustmentOut => 'Penyesuaian Keluar',
            self::TransferOut => 'Transfer Keluar',
            self::Damaged => 'Barang Rusak',
            self::Expired => 'Barang Kadaluarsa',
        };
    }

    public function getColor(): string|array|null{
        return match ($this){
            self::Purchase,
            self::ReturnCustomer,
            self::AdjustmentIn,
            self::TransferIn => 'success',

            self::Sale,
            self::ReturnSupplier,
            self::AdjustmentOut,
            self::TransferOut => 'warning',

            self::Damaged,
            self::Expired => 'danger',
        };
    }

    public function getIcon(): ?string{
        return match ($this){
            self::Purchase => 'heroicon-o-shopping-cart',
            self::ReturnCustomer => 'heroicon-o-arrow-uturn-left',
            self::AdjustmentIn => 'heroicon-o-plus-circle',
            self::TransferIn => 'heroicon-o-arrow-down-tray',

            self::Sale => 'heroicon-o-banknotes',
            self::ReturnSupplier => 'heroicon-o-arrow-uturn-right',
            self::AdjustmentOut => 'heroicon-o-minus-circle',
            self::TransferOut => 'heroicon-o-arrow-up-tray',

            self::Damaged => 'heroicon-o-exclamation-triangle',
            self::Expired => 'heroicon-o-clock',
        };
    }

    public function isIncoming(): bool{
        return in_array($this, self::incomingTypes());
    }

    public function isOutgoing(): bool{
        return in_array($this, self::outgoingTypes());
    }

    public static function incomingTypes(): array{
        return [self::Purchase, self::ReturnCustomer,self::AdjustmentIn, self::TransferIn];
    }

    public static function outgoingTypes(): array{
        return [self::Sale, self::ReturnSupplier, self::AdjustmentOut, self::TransferOut, self::Damaged, self::Expired];
    }

    public static function adjustmentTypes(): array{
        return [
            self::AdjustmentIn,
            self::AdjustmentOut,
            self::Damaged,
            self::Expired,
        ];
    }
}
