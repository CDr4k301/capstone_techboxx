<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    /** @use HasFactory<\Database\Factories\StockHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'component_id',
        'action',
        'quantity_changed',
        'user_id',
    ];
    
    public function user() {
        return $this->hasMany(User::class);
    }

    
}
