<?php

namespace App\Models\Hardware;

use App\Models\BuildCategory;
use App\Models\Supplier;
use App\Models\UserBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Storage extends Model
{
    /** @use HasFactory<\Database\Factories\StorageFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'build_category_id',
        'brand',
        'model',
        'storage_type',
        'interface',
        'capacity_gb',
        'form_factor',
        'read_speed_mbps',
        'write_speed_mbps',
        'price',
        'base_price', // optional field for original/reference price
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
