<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

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

        $row = DB::table($table)->find($id);

        if (!$row) {
            abort(404, 'Product not found');
        }

        // Define table-to-category mapping
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

        // Determine category
        $category = $maps[$table] ?? rtrim($table, 's'); // fallback if missing in map

        // Normalize product (minimal for detail page)
        $rowArr = (array) $row;

        $product = [
            'id'       => $rowArr['id'] ?? 0,
            'name'     => trim(($rowArr['brand'] ?? '') . ' ' . ($rowArr['model'] ?? '')),
            'brand'    => $rowArr['brand'] ?? '',
            'category' => $category,
            'price'    => (float) ($rowArr['price'] ?? 0),
            'stock'    => (int) ($rowArr['stock'] ?? 0),
            'image'    => $rowArr['image'] ?? 'images/placeholder.png',
        ];

        return view('product.show', compact('product'));
    }
}
