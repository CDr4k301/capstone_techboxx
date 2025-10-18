<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Checkout;
use App\Models\OrderedBuild;
use App\Models\UserBuild;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {

        // =============================
        // 1️⃣ From CHECKOUTS (cart_items)
        // =============================

        $componentOrders = collect();

        $allCartItems = Checkout::pluck('cart_item_id'); 

        foreach ($allCartItems as $cartItemId) {
            $cartItem = CartItem::find($cartItemId);

            if ($cartItem) {
                $componentOrders->push([
                    'product_type' => $cartItem->product_type,
                    'product_id'   => $cartItem->product_id,
                    'quantity'     => $cartItem->quantity,
                ]);
            }
        }

        // =============================
        // 2️⃣ From ORDERED BUILDS (user_builds)
        // =============================

        $allOrderedBuilds = OrderedBuild::pluck('user_build_id');

        foreach ($allOrderedBuilds as $userBuildId) {
            $userBuild = UserBuild::find($userBuildId);

            if ($userBuild) {
                $components = [
                    'case'        => $userBuild->pc_case_id,
                    'motherboard' => $userBuild->motherboard_id,
                    'cpu'         => $userBuild->cpu_id,
                    'gpu'         => $userBuild->gpu_id,
                    'storage'     => $userBuild->storage_id,
                    'ram'         => $userBuild->ram_id,
                    'psu'         => $userBuild->psu_id,
                    'cooler'      => $userBuild->cooler_id,
                ];

                foreach ($components as $type => $id) {
                    if ($id) {
                        $componentOrders->push([
                            'product_type' => $type,
                            'product_id'   => $id,
                            'quantity'     => 1,
                        ]);
                    }
                }
            }
        }

        // =============================
        // 3️⃣ Combine and group all results
        // =============================
        $groupedOrders = $componentOrders
            ->groupBy(fn($item) => $item['product_type'] . '-' . $item['product_id'])
            ->map(fn($items) => [
                'product_type' => $items->first()['product_type'],
                'product_id'   => $items->first()['product_id'],
                'total_orders' => $items->sum('quantity'),
            ])
            ->sortByDesc('total_orders')
            ->values();

        // =============================
        // 4️⃣ Enrich grouped results with component details
        // =============================
        $groupedOrdersWithDetails = $groupedOrders->map(function ($item) {
            // Determine the model based on product type
            $model = match ($item['product_type']) {
                'case'        => \App\Models\Hardware\PcCase::class,
                'motherboard' => \App\Models\Hardware\Motherboard::class,
                'cpu'         => \App\Models\Hardware\Cpu::class,
                'gpu'         => \App\Models\Hardware\Gpu::class,
                'storage'     => \App\Models\Hardware\Storage::class,
                'ram'         => \App\Models\Hardware\Ram::class,
                'psu'         => \App\Models\Hardware\Psu::class,
                'cooler'      => \App\Models\Hardware\Cooler::class,
                default       => null,
            };

            $component = $model ? $model::find($item['product_id']) : null;

            // Add details if found
            return [
                'product_type' => ucfirst($item['product_type']),
                'product_id'   => $item['product_id'],
                'total_orders' => $item['total_orders'],
                'product_name' => $component ? ($component->brand . ' ' . $component->model) : 'Unknown',
                'base_price'   => $component->base_price ?? 0,
                'selling_price'=> $component->price ?? 0,
            ];
        });

        // =============================
        // 5️⃣ Combine by product type
        // =============================
        $ordersByType = $groupedOrdersWithDetails
            ->groupBy('product_type')
            ->map(function ($items, $type) {
                return [
                    'product_type' => $type,
                    'total_orders' => $items->sum('total_orders'),
                ];
            })
            ->sortByDesc('total_orders')
            ->values();

        // ---------------------
        // Cart Analysis (keep original approach; compute orders by product_id)
        // ---------------------

        $cartItems = CartItem::all()->groupBy(fn($item) => $item->product_type . '-' . $item->product_id);

        $cartAnalysis = collect();

        foreach ($cartItems as $key => $items) {
            $firstItem = $items->first();
            $productType = $firstItem->product_type;
            $productId = $firstItem->product_id;

            $product = $firstItem->$productType ?? null;

            $name = $product ? trim(($product->brand ?? '') . ' ' . ($product->model ?? '')) : "{$productType} #{$productId}";

            $orderedQty = $items->where('processed', 1)->sum('quantity');
            $addToCartQty = $items->where('processed', 0)->sum('quantity');

            $cartAnalysis->push([
                'name' => $name,
                'ordered_quantity' => $orderedQty,
                'add_to_cart' => $addToCartQty,
            ]);
        }

        // ---------------------
        // Frequent pairs from Python script
        // ---------------------
        $frequentPairs = $this->getFrequentPairsFromPython();

        // --------------------
        // Manual pagination
        // --------------------
        $page = request()->get('page', 1);
        $perPage = 9;
        $paginatedCartAnalysis = new LengthAwarePaginator(
            $cartAnalysis->forPage($page, $perPage),
            $cartAnalysis->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.analytics', [
            'ordersByType' => $ordersByType,
            'cartAnalysis' => $paginatedCartAnalysis,
            'frequentPairs' => $frequentPairs,
        ]);

    }

    private function getFrequentPairsFromPython()
    {
        try {
            $pythonScriptPath = base_path('python_scripts/frequent_pairs.py');
            
            $process = new Process(['python', $pythonScriptPath]);
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            $output = $process->getOutput();
            $result = json_decode($output, true);
            
            if ($result['status'] === 'success') {
                // Convert to collection of objects for the view
                return collect($result['frequentPairs'])->map(function ($pair) {
                    return (object) [
                        'product_a' => $pair['product_a'],
                        'product_b' => $pair['product_b'],
                        'total_price' => $pair['total_price']
                    ];
                });
            }
            
            // Fallback to empty collection if Python script fails
            return collect();
            
        } catch (Exception $e) {
            // Log error and return empty collection
            Log::error('Failed to get frequent pairs from Python script: ' . $e->getMessage());
            return collect();
        }
    }
}
