<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockMovement;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image',
        'is_active',
        'category_id',
        'min_stock',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'min_stock' => 'integer'
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function stockMovements(){
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool{
        return $this->stock <= $this->min_stock;
    }

    public function isOutOfStock(): bool{
        return $this->stock <= 0;
    }

    public function scopeActivity($query){
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query){
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeOutOfStock($query){
        return $query->where('stock', '<=', 0);
    }
}
