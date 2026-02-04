<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'is_active'
    ];

    public function users(){
        return $this->belongsToMany(User::class);
    }

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
