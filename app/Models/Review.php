<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_type',
        'product_table',
        'user_id',
        'name',
        'rating',
        'title',
        'content',
    ];
}