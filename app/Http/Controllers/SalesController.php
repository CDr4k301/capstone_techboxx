<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Checkout;
use App\Models\OrderedBuild;
use App\Models\UserBuild;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $now = Carbon::now('Asia/Manila');

        // ---------------------
        // Date range for period
        // ---------------------
        switch ($period) {
            case 'daily':
                $startDate = $now->copy()->startOfDay();
                $endDate   = $now->copy()->endOfDay();
                break;
        
            case 'weekly':
                $startDate = $now->copy()->startOfWeek(Carbon::MONDAY);
                $endDate   = $now->copy()->endOfWeek(Carbon::SUNDAY);
                break;
        
            case 'annually':
                $startDate = $now->copy()->startOfYear();
                $endDate   = $now->copy()->endOfYear();
                break;
        
            case 'monthly':
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate   = $now->copy()->endOfMonth();
                break;
        }
        
        // =============================
        // 1️⃣ From CHECKOUTS (cart_items)
        // =============================

        $componentSales = collect();

        $paidCartItems = Checkout::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->pluck('cart_item_id'); // this includes duplicates!

        foreach ($paidCartItems as $cartItemId) {
            // we do NOT unique() here — each repeated ID counts again
            $cartItem = CartItem::find($cartItemId);

            if ($cartItem) {
                $componentSales->push([
                    'product_type' => $cartItem->product_type,
                    'product_id'   => $cartItem->product_id,
                    'quantity'     => $cartItem->quantity,
                ]);
            }
        }


        // =============================
        // 2️⃣ From ORDERED BUILDS (user_builds)
        // =============================
        $orderedBuilds = OrderedBuild::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->pluck('user_build_id'); // also includes duplicates

        foreach ($orderedBuilds as $userBuildId) {
            // again, we don’t unique() here — each occurrence counts
            $userBuild = UserBuild::find($userBuildId);

            if ($userBuild) {
                // each build contributes 1 per component
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
                        $componentSales->push([
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
        $groupedSales = $componentSales
            ->groupBy(fn($item) => $item['product_type'] . '-' . $item['product_id'])
            ->map(fn($items) => [
                'product_type' => $items->first()['product_type'],
                'product_id'   => $items->first()['product_id'],
                'total_sold'   => $items->sum('quantity'),
            ])
            ->sortByDesc('total_sold')
            ->values();
        // 🔍 Example inspection
        // dd($groupedSales);

                // =============================
        // 4️⃣ Enrich grouped results with component details
        // =============================

        $groupedSalesWithDetails = $groupedSales->map(function ($item) {
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
                'total_sold'   => $item['total_sold'],
                'product_name' => $component ? ($component->brand . ' ' . $component->model) : 'Unknown',
                'base_price'   => $component->base_price ?? 0,
                'selling_price'=> $component->price ?? 0,
            ];
        });

        // ✅ You can inspect or return this version
        // dd($groupedSalesWithDetails);

        // =============================
        // 5️⃣ Compute totals: total sold, cost of goods, revenue, profit
        // =============================

        $totalSold = $groupedSalesWithDetails->sum('total_sold');

        $totalCostOfGoods = $groupedSalesWithDetails->sum(function ($item) {
            return $item['base_price'] * $item['total_sold'];
        });

        $totalRevenue = $groupedSalesWithDetails->sum(function ($item) {
            return $item['selling_price'] * $item['total_sold'];
        });

        $totalProfit = $totalRevenue - $totalCostOfGoods;

        // Optional: Combine results into a summary object or array
        $summary = [
            'total_sold'      => $totalSold,
            'cost_of_goods'   => $totalCostOfGoods,
            'revenue'         => $totalRevenue,
            'profit'          => $totalProfit,
        ];

        // =============================
        // 6️⃣ Apply product type filter + sorting (NEW)
        // =============================
        $filterType = $request->get('filter_type');

        $filteredSales = $groupedSalesWithDetails;

        // Filter by product_type if given
        if (!empty($filterType)) {
            $filteredSales = $filteredSales->filter(function ($item) use ($filterType) {
                return strtolower($item['product_type']) === strtolower($filterType);
            });
        }

        // Always sort descending by total_sold
        $filteredSales = $filteredSales->sortByDesc('total_sold')->values();


        // Paginate (10 items per page)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 9;
        $pagedData = new LengthAwarePaginator(
            $filteredSales->forPage($currentPage, $perPage),
            $filteredSales->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // ---------------------
        // Sales Overview Chart
        // ---------------------
        if ($period === 'daily') {
            $salesData = Checkout::select(
                    DB::raw('HOUR(created_at) as label'),
                    DB::raw('SUM(total_cost) as total_sales')
                )
                ->whereIn('payment_status', ['paid', 'pending'])  
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('label')
                ->orderBy('label')
                ->get();
            $salesLabels = $salesData->pluck('label')->map(fn($h) => sprintf('%02d:00', $h));
        } elseif ($period === 'weekly') {
            $salesData = Checkout::select(
                    DB::raw('DAYNAME(created_at) as label'),
                    DB::raw('SUM(total_cost) as total_sales')
                )
                ->whereIn('payment_status', ['paid', 'pending'])  
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('label')
                ->orderByRaw("FIELD(label, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                ->get();
            $salesLabels = $salesData->pluck('label');
        } elseif ($period === 'annually') {
            $salesData = Checkout::select(
                    DB::raw('MONTHNAME(created_at) as label'),
                    DB::raw('SUM(total_cost) as total_sales')
                )
                ->whereIn('payment_status', ['paid', 'pending'])  
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('label')
                ->orderBy(DB::raw('MIN(created_at)'))
                ->get();
            $salesLabels = $salesData->pluck('label');
        } else {
            $salesData = Checkout::select(
                    DB::raw('DAY(created_at) as label'),
                    DB::raw('SUM(total_cost) as total_sales')
                )
                ->whereIn('payment_status', ['paid', 'pending'])  
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('label')
                ->orderBy('label')
                ->get();
            $salesLabels = $salesData->pluck('label')->map(fn($d) => 'Day ' . $d);
        }

        $salesTotals = $salesData->pluck('total_sales');

        // dd(Checkout::whereIn('payment_status', ['paid', 'pending'])  
        //   ->whereBetween('created_at', [$startDate, $endDate])
        //   ->get());


        return view('admin.sales', [
            'period' => $period,
            'groupedSalesWithDetails' => $pagedData, // ← now paginated
            'summary' => $summary,
            'filterType' => $filterType,
            'salesLabels' => $salesLabels,      // ← add this
            'salesTotals' => $salesTotals,  // ← add this
        ]);
        


    }


    
}