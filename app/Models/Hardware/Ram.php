<?php

namespace App\Models\Hardware;

use App\Models\BuildCategory;
use App\Models\Supplier;
use App\Models\UserBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ram extends Model
{
    /** @use HasFactory<\Database\Factories\RamFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'build_category_id',
        'brand',
        'model',
        'ram_type',
        'speed_mhz',
        'size_per_module_gb',
        'total_capacity_gb',
        'module_count',
        'is_ecc',
        'is_rgb',
        'price',
        'base_price', // <-- add this if needed
        'stock',
        'image',
        'model_3d',
        'supplier_id',
    ];


    // FETCHING IMAGE FROM DRIVE
// protected $casts = [
    //     'image' => 'array',
    // ];

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
