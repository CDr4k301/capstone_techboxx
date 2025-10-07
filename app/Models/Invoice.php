<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'build_id',
        'customer_id',
        'staff_id',
        'invoice_date',
    ];

    public function checkout()
    {
        return $this->belongsTo(Checkout::class, 'order_id');
    }

    public function build()
    {
        return $this->belongsTo(OrderedBuild::class, 'build_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
