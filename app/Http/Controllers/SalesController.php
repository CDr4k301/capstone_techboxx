<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Checkout;
use App\Models\OrderedBuild;
use App\Models\UserBuild;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $filterType = $request->get('filter_type');
        
        // Use the reusable method
        $data = $this->getSalesData($period, $filterType);
        
        // Paginate the filtered sales data
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 9;
        $pagedData = new LengthAwarePaginator(
            $data['groupedSalesWithDetails']->forPage($currentPage, $perPage),
            $data['groupedSalesWithDetails']->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.sales', [
            'period' => $period,
            'groupedSalesWithDetails' => $pagedData,
            'summary' => $data['summary'],
            'filterType' => $filterType,
            'salesLabels' => $data['salesLabels'],
            'salesTotals' => $data['salesTotals'],
            'xAxisLabel' => $data['xAxisLabel'],
        ]);
    }

    private function getSalesData($period, $filterType = null)
    {
        // Set date range based on period
        $now = Carbon::now('Asia/Manila');
        switch ($period) {
            case 'daily':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'weekly':
                $startDate = $now->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $now->copy()->endOfWeek(Carbon::SUNDAY);
                break;
            case 'annually':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            default: // monthly
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
        }

        // Get sales data from checkouts and ordered builds
        $componentSales = collect();

        // 1. From CHECKOUTS (cart_items)
        $paidCartItems = Checkout::where('payment_status', 'paid')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->pluck('cart_item_id');

        foreach ($paidCartItems as $cartItemId) {
            $cartItem = CartItem::find($cartItemId);
            if ($cartItem) {
                $componentSales->push([
                    'product_type' => $cartItem->product_type,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                ]);
            }
        }

        // 2. From ORDERED BUILDS (user_builds)
        $orderedBuilds = OrderedBuild::where('payment_status', 'paid')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->pluck('user_build_id');

        foreach ($orderedBuilds as $userBuildId) {
            $userBuild = UserBuild::find($userBuildId);
            if ($userBuild) {
                $components = [
                    'case' => $userBuild->pc_case_id,
                    'motherboard' => $userBuild->motherboard_id,
                    'cpu' => $userBuild->cpu_id,
                    'gpu' => $userBuild->gpu_id,
                    'storage' => $userBuild->storage_id,
                    'ram' => $userBuild->ram_id,
                    'psu' => $userBuild->psu_id,
                    'cooler' => $userBuild->cooler_id,
                ];

                foreach ($components as $type => $id) {
                    if ($id) {
                        $componentSales->push([
                            'product_type' => $type,
                            'product_id' => $id,
                            'quantity' => 1,
                        ]);
                    }
                }
            }
        }

        // 3. Combine and group all results
        $groupedSales = $componentSales
            ->groupBy(fn($item) => $item['product_type'] . '-' . $item['product_id'])
            ->map(fn($items) => [
                'product_type' => $items->first()['product_type'],
                'product_id' => $items->first()['product_id'],
                'total_sold' => $items->sum('quantity'),
            ])
            ->sortByDesc('total_sold')
            ->values();

        // 4. Enrich grouped results with component details
        $groupedSalesWithDetails = $groupedSales->map(function ($item) {
            $model = match ($item['product_type']) {
                'case' => \App\Models\Hardware\PcCase::class,
                'motherboard' => \App\Models\Hardware\Motherboard::class,
                'cpu' => \App\Models\Hardware\Cpu::class,
                'gpu' => \App\Models\Hardware\Gpu::class,
                'storage' => \App\Models\Hardware\Storage::class,
                'ram' => \App\Models\Hardware\Ram::class,
                'psu' => \App\Models\Hardware\Psu::class,
                'cooler' => \App\Models\Hardware\Cooler::class,
                default => null,
            };

            $component = $model ? $model::find($item['product_id']) : null;

            return [
                'product_type' => ucfirst($item['product_type']),
                'product_id' => $item['product_id'],
                'total_sold' => $item['total_sold'],
                'product_name' => $component ? ($component->brand . ' ' . $component->model) : 'Unknown',
                'base_price' => $component->base_price ?? 0,
                'selling_price' => $component->price ?? 0,
            ];
        });

        // 5. Compute summary
        $totalSold = $groupedSalesWithDetails->sum('total_sold');
        $totalCostOfGoods = $groupedSalesWithDetails->sum(function ($item) {
            return $item['base_price'] * $item['total_sold'];
        });
        $totalRevenue = $groupedSalesWithDetails->sum(function ($item) {
            return $item['selling_price'] * $item['total_sold'];
        });
        $totalProfit = $totalRevenue - $totalCostOfGoods;

        $summary = [
            'total_sold' => $totalSold,
            'cost_of_goods' => $totalCostOfGoods,
            'revenue' => $totalRevenue,
            'profit' => $totalProfit,
        ];

        // 6. Apply product type filter
        $filteredSales = $groupedSalesWithDetails;
        if (!empty($filterType)) {
            $filteredSales = $filteredSales->filter(function ($item) use ($filterType) {
                return strtolower($item['product_type']) === strtolower($filterType);
            });
        }
        $filteredSales = $filteredSales->sortByDesc('total_sold')->values();

        // 7. Get chart data
        $chartData = $this->getChartData($period, $startDate, $endDate);

        return [
            'period' => $period,
            'summary' => $summary,
            'groupedSalesWithDetails' => $filteredSales,
            'salesLabels' => $chartData['labels'],
            'salesTotals' => $chartData['totals'],
            'xAxisLabel' => $chartData['xAxisLabel']
        ];
    }

    private function getChartData($period, $startDate, $endDate)
    {
        if ($period === 'daily') {
            $salesData = Checkout::select(
                    DB::raw('HOUR(updated_at) as label'),
                    DB::raw('SUM(total_cost) as total_sales')
                )
                ->whereIn('payment_status', ['paid', 'pending'])  
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->groupBy('label')
                ->unionAll(
                    OrderedBuild::join('user_builds', 'ordered_builds.user_build_id', '=', 'user_builds.id')
                        ->select(
                            DB::raw('HOUR(ordered_builds.updated_at) as label'),
                            DB::raw('SUM(user_builds.total_price) as total_sales')
                        )
                        ->whereIn('ordered_builds.payment_status', ['paid', 'pending'])  
                        ->whereBetween('ordered_builds.updated_at', [$startDate, $endDate])
                        ->groupBy('label')
                )
                ->orderBy('label')
                ->get();

            $labels = $salesData->pluck('label')->map(fn($h) => sprintf('%02d:00', $h));
            $xAxisLabel = 'Time of Day';
        } elseif ($period === 'weekly') {
            $salesData = Checkout::select(
                    DB::raw('DAYNAME(updated_at) as label'),
                    DB::raw('SUM(total_cost) as total_sales')
                )
                ->whereIn('payment_status', ['paid', 'pending'])  
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->groupBy('label')
                ->orderByRaw("FIELD(label, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                ->get();
            $labels = $salesData->pluck('label');
            $xAxisLabel = 'Days of Week';
        } elseif ($period === 'annually') {
            $salesData = Checkout::select(
                    DB::raw('MONTHNAME(updated_at) as label'),
                    DB::raw('SUM(total_cost) as total_sales')
                )
                ->whereIn('payment_status', ['paid', 'pending'])  
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->groupBy('label')
                ->orderBy(DB::raw('MIN(updated_at)'))
                ->get();
            $labels = $salesData->pluck('label');
            $xAxisLabel = 'Months';
        } else {
            $salesData = Checkout::select(
                    DB::raw('DAY(updated_at) as label'),
                    DB::raw('SUM(total_cost) as total_sales')
                )
                ->whereIn('payment_status', ['paid', 'pending'])  
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->groupBy('label')
                ->orderBy('label')
                ->get();
            $labels = $salesData->pluck('label')->map(fn($d) => 'Day ' . $d);
            $xAxisLabel = 'Days of Month';
        }

        $totals = $salesData->pluck('total_sales');

        return [
            'labels' => $labels,
            'totals' => $totals,
            'xAxisLabel' => $xAxisLabel
        ];
    }

    public function downloadSalesReport(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $filterType = $request->get('filter_type');
        
        // Use the reusable method
        $data = $this->getSalesData($period, $filterType);

        // Generate PDF
        $pdf = Pdf::loadView('admin.sales-pdf', [
            'period' => $period,
            'summary' => $data['summary'],
            'products' => $data['groupedSalesWithDetails'],
            'generatedDate' => now()->format('F j, Y g:i A')
        ]);

        // Download PDF
        return $pdf->download('sales-report-' . $period . '-' . now()->format('Y-m-d') . '.pdf');
    }
}