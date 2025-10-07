<?php

namespace App\Models\Hardware;

use App\Models\BuildCategory;
use App\Models\Supplier;
use App\Models\UserBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cooler extends Model
{
    /** @use HasFactory<\Database\Factories\CoolerFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'build_category_id',
        'brand',
        'model',
        'cooler_type',
        'socket_compatibility',
        'max_tdp',
        'radiator_size_mm',
        'fan_count',
        'height_mm',
        'price',
        'base_price', // <- added this
        'stock',
        'image',
        'model_3d',
        'supplier_id',
    ];

    // FETCHING IMAGE FROM DRIVE
    protected $casts = [
        'socket_compatibility' => 'array',
    ];

    // DEFINE RELATIONSHIP
    public function buildCategory() {
        return $this->belongsTo(BuildCategory::class);
    }

    public function userBuild() {
        return $this->hasMany(UserBuild::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }
}
