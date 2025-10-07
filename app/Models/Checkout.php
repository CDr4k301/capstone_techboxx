<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    /** @use HasFactory<\Database\Factories\CheckoutFactory> */
    use HasFactory;

    protected $fillable = [
        'cart_item_id',
        'checkout_date',
        'total_cost',
        'payment_method',
        'payment_status',
        'pickup_status',
        'pickup_date',
    ];

    protected $casts = [
        'checkout_date' => 'datetime',
        'pickup_date' => 'datetime',
    ];

    public function cartItem() {
        return $this->belongsTo(CartItem::class, 'cart_item_id');
    }

    public function invoice() {
        return $this->hasOne(Invoice::class, 'order_id');
    }
    
}
