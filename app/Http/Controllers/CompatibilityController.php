<?php

namespace App\Http\Controllers;

use App\Services\CompatibilityService;
use Illuminate\Http\Request;

class CompatibilityController extends Controller
{
    protected CompatibilityService $compatibilityService;

    public function __construct(CompatibilityService $compatibilityService)
    {
        $this->compatibilityService = $compatibilityService;
    }
    public function checkCpuCompatibility(Request $request)
    {
        $cpuId = $request->input('cpu_id');
        $motherboardId = $request->input('motherboard_id');

        $cpu = \App\Models\Hardware\Cpu::findOrFail($cpuId);
        $motherboard = \App\Models\Hardware\Motherboard::findOrFail($motherboardId);

        $compatible = $this->compatibilityService->isCpuCompatiblewithMotherboard($cpu, $motherboard);

        return response()->json([
            'cpu' => $cpu->model_name,
            'motherboard' => $motherboard->model_name,
            'compatible' => $compatible,
        ]);
    }
}
