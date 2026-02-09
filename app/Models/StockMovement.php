<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\User;
use App\Enums\StockMovementType;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_id',
        'reference_type',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'type' => StockMovementType::class,
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function reference(){
        return $this->morphTo();
    }

    public function isIncoming(): bool{
        return $this->type->isIncoming();
    }

    public function isOutgoing(): bool{
        return $this->type->isOutgoing();
    }

    public function scopeIncoming($query){
        return $query->whereIn('type', StockMovementType::incomingTypes());
    }

    public function scopeOutgoing($query){
        return $query->whereIn('type', StockMovementType::outgoingTypes());
    }

    public function scopeForProduct($query, int $productId){
        return $query->where('product_id', $productId);
    }

    public function scopeInDateRange($query, $stardDate, $endDate){
        return $query->whereBetween('created_at', [$stardDate, $endDate]);
    }
}
