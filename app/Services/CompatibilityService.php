<?php

namespace App\Services;

use App\Models\Hardware\Cooler;
use App\Models\Hardware\Cpu;
use App\Models\Hardware\Gpu;
use App\Models\Hardware\Motherboard;
use App\Models\Hardware\PcCase;
use App\Models\Hardware\Psu;
use App\Models\Hardware\Ram;
use App\Models\Hardware\Storage;
use App\Models\Hardware\SupportedCpu;

class CompatibilityService
{
    private array $caseSupportMap = [
        'ATX' => ['ATX', 'Micro-ATX', 'Mini-ITX'],
        'Micro-ATX' => ['Micro-ATX', 'Mini-ITX'],
        'Mini-ITX' => ['Mini-ITX'],
        'E-ATX' => ['E-ATX', 'ATX', 'Micro-ATX', 'Mini-ITX'],
    ];


    // CPU - MOTHERBOARD
    public function isCpuCompatiblewithMotherboard(Cpu $cpu, Motherboard $motherboard,SupportedCpu $supported_cpu)
    {
        $results = ['errors' => [], 'warnings' => []];
        if ($cpu->socket_type !== $motherboard->socket_type) {
            $results['errors'][]= "❌CPU and motherboard socket_type is incompatible.";
        }
        //motherboard supports cpu fallback
        if (!empty($supported_cpu->cpuID)) {
            // Compare names
            $cpuArray = array_map('trim', explode(',', $cpuList));
            foreach ($cpuList as $supportedCpu) {
                if (stripos($supportedCpu['Name'], $cpu->model_name) == false) {
                    return $results;
                }
            }
           $results['errors'][]= "❌Motherboard Doesn't Support CPU";
        }
        return $results;
    }

    
    // RAM - MOTHERBOARD
    public function isRamCompatiblewithMotherboard(Ram $ram, Motherboard $motherboard)
    {
        $results = ['errors' => [], 'warnings' => []];

        //Check's the MOBO and RAM's RAM type if the same
        if($ram->ram_type !== $motherboard->ram_type){
             $results['errors'][] = "❌RAM and motherboard ram type is incompatible.";
        }
        //Check's the RAM's RAM size if it is less than or the same as MOBO's
        if($ram->total_capacity_gb > $motherboard -> max_ram){
             $results['errors'][] = "❌RAM capacity ({$ram->total_capacity_gb} GB) exceeds the motherboard's max supported capacity ({$motherboard->max_ram} GB). System might not boot!";
        }
        //Check's the RAM's speed if it is less than or the same as MOBO's
        if ($ram->speed_mhz > $motherboard -> max_ram_speed) {
             $results['warnings'][] = "⚠️ RAM speed ({$ram->speed_mhz} MHz) is higher than the motherboard's max supported speed ({$motherboard->max_ram_speed} MHz). It will run at the lower speed.";
        }
        return  $results;
    }


    // GPU - CASE
    public function isGpuCompatiblewithCase(Gpu $gpu, PcCase $case)
    {
        $results = ['errors' => [], 'warnings' => []];

        // GPU length vs case clearance
        if ($gpu->length_mm >= $case->max_gpu_length_mm) {
            $results['errors'][] = "❌GPU and Case GPU length is incompatible.";
        }
        return $results;
    }


    // COOLER - CPU AND CASE
    public function isCoolerCompatible(Cooler $cooler, Motherboard $motherboard, PcCase $case)
    {
        $results = ['errors' => [], 'warnings' => []];

        // Check socket support
        if (!in_array($motherboard->socket_type, $cooler->supported_sockets)) {
            $results['errors'][] = "❌ Cooler does not support CPU socket type ({$motherboard->socket_type}).";
        }
        // Check cooler height vs case clearance
        if ($cooler->height_mm > $case->max_cooler_height_mm) {
            $results['errors'][] = "❌ Cooler height ({$cooler->height_mm}mm) exceeds case limit ({$case->max_cooler_height_mm}mm).";
        } 
        return $results;
    }


    // PSU - CPU + GPU
    public function isPsuEnough(Psu $psu, Cpu $cpu, Gpu $gpu)
    {
        $results = ['errors' => [], 'warnings' => []];

        $estimatedPower = $cpu->tdp + $gpu->power_draw_watts + 150; // // 150W for motherboard, RAM, storage
        if ($psu->wattage < $estimatedPower) {
            $results['warnings'][] = "⚠️ PSU wattage ({$psu->wattage}W) is close to estimated system draw ({$estimatedPower}W). Consider a higher wattage PSU.";
        }
        return $results;
    }

    // MOTHERBOARD - CASE
    public function isMotherboardCompatiblewithCase(Motherboard $motherboard, PcCase $case)
    {
        $results = ['errors' => [], 'warnings' => []];

        $supported = $this->caseSupportMap[$case->form_factor_support] ?? [];
        if (!in_array($motherboard->form_factor, $supported)) {
            $results['errors'][] = "❌ Case does not support motherboard form factor ({$motherboard->form_factor}).";
        }
        return $results;
    }

    // STORAGE - MOTHERBOARD
    public function isStorageCompatiblewithMotherboard(Motherboard $motherboard, Storage $storage)
    {
        $results = ['errors' => [], 'warnings' => []];

        if ($storage->interface === 'M.2') {
            if ($motherboard->m2_slots < 1) {
                $results['errors'][] = "❌ Motherboard has no M.2 slots for this storage device.";
            }
        }

        if ($storage->interface === 'SATA') {
            if ($motherboard->sata_ports < 1) {
                $results['errors'][] = "❌ Motherboard has no available SATA ports for this drive.";
            }
        }
        return $results;
    }
}