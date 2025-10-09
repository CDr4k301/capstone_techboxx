<?php
// app/Services/MBAnalysisService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MBAnalysisService
{
    public function getRecommendations($productName, $productType)
    {
        try {
            $pythonScript = base_path('python_scripts/mba_analysis.py');
            
            // Escape product name for command line
            $escapedName = escapeshellarg($productName);
            $escapedType = escapeshellarg($productType);
            
            $command = "python {$pythonScript} recommend {$escapedName} {$escapedType} 2>&1";
            
            $output = shell_exec($command);
            
            if ($output) {
                $result = json_decode(trim($output), true);
                
                if (json_last_error() === JSON_ERROR_NONE && isset($result['status']) && $result['status'] === 'success') {
                    return $result['recommendations'] ?? [];
                }
            }
            
            Log::error('MBA analysis failed', ['output' => $output]);
            return [];
            
        } catch (\Exception $e) {
            Log::error('MBA service error: ' . $e->getMessage());
            return [];
        }
    }
    
    // Fallback recommendations if Python script fails
    public function getFallbackRecommendations($productType)
    {
        // Simple fallback based on product type
        $fallbacks = [
            'cpu' => [
                ['name' => 'Compatible Motherboard', 'type' => 'motherboard', 'price' => 0],
                ['name' => 'RAM Kit', 'type' => 'ram', 'price' => 0],
                ['name' => 'CPU Cooler', 'type' => 'cooler', 'price' => 0]
            ],
            'gpu' => [
                ['name' => 'Power Supply', 'type' => 'psu', 'price' => 0],
                ['name' => 'PC Case', 'type' => 'pc_case', 'price' => 0]
            ],
            'motherboard' => [
                ['name' => 'Compatible CPU', 'type' => 'cpu', 'price' => 0],
                ['name' => 'RAM', 'type' => 'ram', 'price' => 0]
            ],
            'ram' => [
                ['name' => 'Motherboard', 'type' => 'motherboard', 'price' => 0],
                ['name' => 'CPU', 'type' => 'cpu', 'price' => 0]
            ]
        ];
        
        return $fallbacks[$productType] ?? [];
    }
}