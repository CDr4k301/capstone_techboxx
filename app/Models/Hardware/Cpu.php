<?php

namespace App\Models\Hardware;

use App\Models\BuildCategory;
use App\Models\Supplier;
use App\Models\UserBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cpu extends Model
{
    /** @use HasFactory<\Database\Factories\CpuFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'build_category_id',
        'brand',
        'model',
        'socket_type',
        'cores',
        'threads',
        'base_clock',
        'boost_clock',
        'tdp',
        'integrated_graphics',
        'generation',
        'price',
        'base_price', // <- added this
        'stock',
        'image',
        'model_3d',
        'supplier_id',
    ];

    // DEFINE RELATIONSHIPS
    public function buildCategory() {
        return $this->belongsTo(BuildCategory::class);
    }

    public function userBuild() {
        return $this->hasMany(UserBuild::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function supportedCpu() {
        return $this->hasMany(Motherboard::class);
    }
}
