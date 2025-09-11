<?php

namespace App\Models\Hardware;

use App\Models\BuildCategory;
use App\Models\UserBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gpu extends Model
{
    /** @use HasFactory<\Database\Factories\GpuFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'build_category_id',
        'brand',
        'model',
        'vram_gb',
        'power_draw_watts',
        'recommended_psu_watt',
        'length_mm',
        'pcie_interface',
        'connectors_required',
        'price',
        'stock',
        'image',
        'model_3d',
    ];

    // FETCHING IMAGE FROM DRIVE
    protected $casts = [
        'image' => 'array',
    ];

    public function buildCategory() {
        return $this->belongsTo(BuildCategory::class);
    }

    public function userBuild() {
        return $this->hasMany(UserBuild::class);
    }
}
