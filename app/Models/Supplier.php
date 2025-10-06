<?php

namespace App\Models;

use App\Models\Hardware\Cooler;
use App\Models\Hardware\Cpu;
use App\Models\Hardware\Gpu;
use App\Models\Hardware\Motherboard;
use App\Models\Hardware\PcCase;
use App\Models\Hardware\Psu;
use App\Models\Hardware\Ram;
use App\Models\Hardware\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'is_active',
    ];

    public function brands() {
        return $this->hasMany(Brand::class);
    }

    public function motherboard() {
        return $this->hasMany(Motherboard::class);
    }

    public function cpus() {
        return $this->hasMany(Cpu::class);
    }

    public function case() {
        return $this->hasMany(PcCase::class);
    }

    public function gpu() {
        return $this->hasMany(Gpu::class);
    }

    public function psu() {
        return $this->hasMany(Psu::class);

    }

    public function storage() {
        return $this->hasMany(Storage::class);
    }

    public function ram() {
        return $this->hasMany(Ram::class);
    }
    
    public function cooler() {
        return $this->hasMany(Cooler::class);
    }

}