<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\MBAnalysisService;

class CatalogueController extends Controller
{
    public function index(Request $request)
    {
        // 1) Pull from all component tables that exist
        $maps = [
            'cpus'         => 'cpu',
            'gpus'         => 'gpu',
            'motherboards' => 'motherboard',
            'rams'         => 'ram',
            'storages'     => 'storage',
            'psus'         => 'psu',
            'pc_cases'     => 'case',     // ok even if table doesn't exist; we skip it below
            'coolers'      => 'cooler',
        ];

        $all = collect();

        foreach ($maps as $table => $category) {
            if (!Schema::hasTable($table)) {
                continue; // skip cleanly if teammate didn't migrate this table yet
            }

            // Grab all rows (all fields)
            $rows = DB::table($table)->get();

            // Normalize every row into a single shape for the Blade
            $normalized = $rows->map(function ($row) use ($category, $table) {
            $rowArr = (array) $row;

            $reviewStats = DB::table('reviews')
                ->where('product_id', $rowArr['id'])
                ->where('product_type', $table)
                ->selectRaw('COALESCE(AVG(rating), 0) as average_rating, COUNT(*) as reviews_count')
                ->first();

            // Common fields
            $common = [
                'id'             => (int) ($rowArr['id'] ?? 0),
                'component_type' => strtolower($category),
                'table'          => $table,
                'name'           => trim(($rowArr['brand'] ?? '') . ' ' . ($rowArr['model'] ?? '')),
                'brand'          => (string) ($rowArr['brand'] ?? ''),
                'category'       => $category,
                'price'          => (float) ($rowArr['price'] ?? 0),
                'stock'          => (int) ($rowArr['stock'] ?? 0),
                'image'          => $rowArr['image'] ?? 'images/placeholder.png',
                'created_at'     => $rowArr['created_at'] ?? now(),
                'rating'         => (int) ($reviewStats->average_rating ?? 0),
                'reviews_count'  => (int) ($reviewStats->reviews_count ?? 0),
            ];

            // ✅ Specs mapping per category
            switch ($category) {
                case 'cpu':
                    $specs = [
                        'brand'               => $rowArr['brand'] ?? '',
                        'model'               => $rowArr['model'] ?? '',
                        'socket_type'         => $rowArr['socket_type'] ?? '',
                        'cores'               => $rowArr['cores'] ?? '',
                        'threads'             => $rowArr['threads'] ?? '',
                        'base_clock'          => number_format($rowArr['base_clock'] ?? 0, 1),
                        'boost_clock'         => number_format($rowArr['boost_clock'] ?? 0, 1),
                        'tdp'                 => $rowArr['tdp'] ?? '',
                        'has integrated graphics' => isset($rowArr['integrated_graphics']) ? ($rowArr['integrated_graphics'] ? 'Yes' : 'No') : 'N/A',
                        'generation'          => $rowArr['generation'] ?? '',
                        'price'               => '₱' . number_format($rowArr['price'] ?? 0, 2),
                        'stock'               => $rowArr['stock'] ?? '',
                    ];
                    break;

                case 'gpu':
                    $specs = [
                        'brand'       => $rowArr['brand'] ?? '',
                        'model'       => $rowArr['model'] ?? '',
                        'vram_gb' => ($rowArr['vram_gb'] ?? '') . ' GB',
                        'power draw watts'  => ($rowArr['power_draw_watts'] ?? '') . ' W',
                        'recommended psu watt' => ($rowArr['recommended_psu_watt'] ?? '') . ' W',
                        'length_mm'         => ($rowArr['length_mm'] ?? '') . ' mm',
                        'pcie_interface'  => $rowArr['pcie_interface'] ?? '',
                        'connectors_required'       => $rowArr['connectors_required'] ?? '',
                        'price'               => '₱' . number_format($rowArr['price'] ?? 0, 2),
                        'stock'       => $rowArr['stock'] ?? '',
                    ];
                    break;

                case 'motherboard':
                    $specs = [
                        'brand'        => $rowArr['brand'] ?? '',
                        'model'        => $rowArr['model'] ?? '',
                        'socket_type'  => $rowArr['socket_type'] ?? '',
                        'form_factor'  => $rowArr['form_factor'] ?? '',
                        'chipset'      => $rowArr['chipset'] ?? '',
                        'ram_type' => $rowArr['ram_type'] ?? '',
                        'max_ram'   => ($rowArr['max_ram'] ?? '') . ' GB',
                        'max_ram speed'   => ($rowArr['max_ram_speed'] ?? '') . ' MHz',
                        'pcie_slots'   => $rowArr['pcie_slots'] ?? '',
                        'm2_slots'   => $rowArr['m2_slots'] ?? '',
                        'sata_ports'   => $rowArr['sata_ports'] ?? '',
                        'usb_ports'   => $rowArr['usb_ports'] ?? '',
                        'has wifi onboard'   => isset($rowArr['wifi_onboard']) ? ($rowArr['wifi_onboard'] ? 'Yes' : 'No') : 'N/A',
                        'price'               => '₱' . number_format($rowArr['price'] ?? 0, 2),
                        'stock'        => $rowArr['stock'] ?? '',
                    ];
                    break;

                case 'ram':
                    $specs = [
                        'brand'    => $rowArr['brand'] ?? '',
                        'model'    => $rowArr['model'] ?? '',
                        'ram_type' => $rowArr['ram_type'] ?? '',
                        'speed_mhz'    => ($rowArr['speed_mhz'] ?? '') . ' MHz',
                        'size_per_module_gb'     => ($rowArr['size_per_module_gb'] ?? '') . ' GB',
                        'total_capacity_gb'  => ($rowArr['total_capacity_gb'] ?? '') . ' GB',
                        'module_count'  => $rowArr['module_count'] ?? '',
                        'is_ecc'  => $rowArr['is_ecc'] ?? '',
                        'is_rgb'  => $rowArr['is_rgb'] ?? '',
                        'price'    => $rowArr['price'] ?? '',
                        'stock'    => $rowArr['stock'] ?? '',
                    ];
                    break;

                case 'storage':
                    $specs = [
                        'brand'     => $rowArr['brand'] ?? '',
                        'model'     => $rowArr['model'] ?? '',
                        'capacity'  => $rowArr['capacity'] ?? '',
                        'type'      => $rowArr['type'] ?? '',
                        'interface' => $rowArr['interface'] ?? '',
                        'price'     => $rowArr['price'] ?? '',
                        'stock'     => $rowArr['stock'] ?? '',
                    ];
                    break;

                case 'psu':
                    $specs = [
                        'brand'      => $rowArr['brand'] ?? '',
                        'model'      => $rowArr['model'] ?? '',
                        'wattage'    => $rowArr['wattage'] ?? '',
                        'efficiency' => $rowArr['efficiency'] ?? '',
                        'modular'    => $rowArr['modular'] ?? '',
                        'price'      => $rowArr['price'] ?? '',
                        'stock'      => $rowArr['stock'] ?? '',
                    ];
                    break;

                case 'cooler':
                    $specs = [
                        'brand'       => $rowArr['brand'] ?? '',
                        'model'       => $rowArr['model'] ?? '',
                        'type'        => $rowArr['type'] ?? '',
                        'fan_size'    => $rowArr['fan_size'] ?? '',
                        'fan_speed'   => $rowArr['fan_speed'] ?? '',
                        'noise_level' => $rowArr['noise_level'] ?? '',
                        'tdp_support' => $rowArr['tdp_support'] ?? '',
                        'price'       => $rowArr['price'] ?? '',
                        'stock'       => $rowArr['stock'] ?? '',
                    ];
                    break;

                case 'case':
                    $specs = [
                        'brand'       => $rowArr['brand'] ?? '',
                        'model'       => $rowArr['model'] ?? '',
                        'form_factor' => $rowArr['form_factor'] ?? '',
                        'color'       => $rowArr['color'] ?? '',
                        'dimensions'  => $rowArr['dimensions'] ?? '',
                        'price'       => $rowArr['price'] ?? '',
                        'stock'       => $rowArr['stock'] ?? '',
                    ];
                    break;

                default:
                    $specs = [
                        'brand' => $rowArr['brand'] ?? '',
                        'model' => $rowArr['model'] ?? '',
                        'price' => $rowArr['price'] ?? '',
                        'stock' => $rowArr['stock'] ?? '',
                    ];
            }

            $common['specs'] = $specs;

            return $common;
        });

            $all = $all->merge($normalized);
        }

        // 2) Filters
        $filtered = $all;

        // Search
        if ($request->filled('search')) {
            $q = mb_strtolower($request->input('search'));
            $filtered = $filtered->filter(function ($item) use ($q) {
                $hay = mb_strtolower($item['name'] . ' ' . $item['brand'] . ' ' . $item['category']);
                return strpos($hay, $q) !== false;
            });
        }

        // Category
        if ($request->filled('category')) {
            $filtered = $filtered->filter(fn ($i) => $i['category'] === $request->category);
        }

        // Brand
        if ($request->filled('brands')) {
            $selectedBrands = (array) $request->brands;
            $filtered = $filtered->filter(fn ($i) => in_array($i['brand'], $selectedBrands));
        }

        // Price range
        if ($request->filled('min_price')) {
            $min = (float) $request->min_price;
            $filtered = $filtered->filter(fn ($i) => $i['price'] >= $min);
        }
        if ($request->filled('max_price')) {
            $max = (float) $request->max_price;
            $filtered = $filtered->filter(fn ($i) => $i['price'] <= $max);
        }

        // 3) Sorting
        switch ($request->input('sort')) {
            case 'newest':
                $filtered = $filtered->sortByDesc('created_at');
                break;
            case 'price_asc':
                $filtered = $filtered->sortBy('price');
                break;
            case 'price_desc':
                $filtered = $filtered->sortByDesc('price');
                break;
            case 'name_asc':
                $filtered = $filtered->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
                break;
            case 'name_desc':
                $filtered = $filtered->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
                break;
            default:
                // default order = newest
                $filtered = $filtered->sortByDesc('created_at');
        }

        // 4) Sidebar data (from ALL data so filters don't hide options)
        $categories = $all->pluck('category')->unique()->values();
        if ($request->filled('category')) {
            // only brands that exist in this category
            $brands = $all->where('category', $request->category)
                        ->pluck('brand')
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();
        } else {
            // all brands
            $brands = $all->pluck('brand')->filter()->unique()->sort()->values();
        }

        // 5) Pagination (manual, because we used Collections)
        $perPage = 12; // feel free to adjust
        $page    = (int) ($request->get('page', 1));
        $total   = $filtered->count();
        $items   = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

        $products = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('catalogue', compact('products', 'categories', 'brands'));
    }

    public function show($table, $id)
{
    if (!Schema::hasTable($table)) {
        abort(404, 'Table not found');
    }

    $columns = Schema::getColumnListing($table);
    $row = DB::table($table)->find($id);

    if (!$row) {
        abort(404, 'Product not found');
    }

    // Define table-specific common columns
    $commonColumns = $this->getCommonColumnsForTable($table);

    // Fetch related data for specific tables
    $relatedData = $this->getRelatedData($table, $id);

    $maps = [
        'cpus'         => 'cpu',
        'gpus'         => 'gpu', 
        'motherboards' => 'motherboard',
        'rams'         => 'ram',
        'storages'     => 'storage',
        'psus'         => 'psu',
        'pc_cases'     => 'case',
        'coolers'      => 'cooler',
    ];

    $category = $maps[$table] ?? rtrim($table, 's');
    $rowArr = (array) $row;

    $product = [
        'id'       => $rowArr['id'] ?? 0,
        'name'     => trim(($rowArr['brand'] ?? '') . ' ' . ($rowArr['model'] ?? '')),
        'brand'    => $rowArr['brand'] ?? '',
        'category' => $category,
        'price'    => (float) ($rowArr['price'] ?? 0),
        'stock'    => (int) ($rowArr['stock'] ?? 0),
        'image'    => $rowArr['image'] ?? 'images/placeholder.png',
        'description' => $rowArr['description'] ?? 'No description available.',
    ];

    // ✅ Get reviews linked to this product
    $reviews = Review::where('product_id', $product['id'])
                ->where('product_type', $table)
                ->latest()
                ->get();

    // ✅ Get MBA recommendations
    $mbaService = new MBAnalysisService();
    $mbaRecommendations = $mbaService->getRecommendations($product['name'], $category);
    
    // If no MBA recommendations, use fallback
    if (empty($mbaRecommendations)) {
        $mbaRecommendations = $mbaService->getFallbackRecommendations($category);
    }

    return view('product.show', compact(
        'product', 
        'row', 
        'columns', 
        'table', 
        'commonColumns', 
        'relatedData', 
        'reviews',
        'mbaRecommendations'
    ));
}

    private function getCommonColumnsForTable($table)
    {
        $columnGroups = [
            'common' => ['brand', 'model', 'price', 'stock'],
            
            'cpus' => [
                'socket_type', 'cores', 'threads', 'base_clock', 'boost_clock', 
                'tdp', 'integrated_graphics', 'generation'
            ],
            
            'gpus' => [
                'vram_gb', 'power_draw_watts', 'recommended_psu_watt', 'length_mm', 'pcie_interface',
                'pcie_interface'
            ],
            
            'motherboards' => [
                'socket_type','chipset', 'form_factor', 'ram_type', 'max_ram',
                'ram_slots', 'max_ram_speed', 'pcie_slots', 'm2_slots', 'sata_ports', 'usb_ports', 'wifi_onboard'
            ],
            
            'rams' => [
                'ram_type', 'speed_mhz', 'size_per_module_gb', 'total_capacity_gb', 'module_count', 'is_ecc',
                'is_rgb', 
            ],
            
            'storages' => [
                'storage_type', 'interface', 'capacity_gb', 'form_factor', 'read_speed_mbps', 'write_speed_mbps'
            ],
            
            'psus' => [
                'wattage', 'efficiency_rating', 'modular', 'pcie_connectors', 'sata_connectors'
            ],
            
            'pc_cases' => [
                'form_factor_support', 'max_gpu_length_mm', 'max_cooler_height_mm', 'fan_mounts',
            ],
            
            'coolers' => [
                'cooler_type', 'socket_compatibility', 'max_tdp', 'radiator_size_mm', 'fan_count',
                'height_mm'
            ]
        ];

        return array_merge(
            $columnGroups['common'],
            $columnGroups[$table] ?? []
        );
    }

    private function getRelatedData($table, $id)
    {
        $relatedData = [];
        
        switch($table) {
            case 'pc_cases':
                // Check if related tables exist before querying
                if (Schema::hasTable('pc_case_drive_bays')) {
                    $relatedData['drive_bays'] = DB::table('pc_case_drive_bays')
                        ->where('pc_case_id', $id)
                        ->first();
                }
                
                if (Schema::hasTable('pc_case_front_usb_ports')) {
                    $relatedData['front_ports'] = DB::table('pc_case_front_usb_ports')
                        ->where('pc_case_id', $id)
                        ->first();
                }
                
                if (Schema::hasTable('pc_case_radiator_supports')) {
                    $relatedData['radiator_support'] = DB::table('pc_case_radiator_supports')
                        ->where('pc_case_id', $id)
                        ->get();
                }
                break;
            
        }
        
        return $relatedData;
    }

}
