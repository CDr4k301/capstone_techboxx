<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $totalOrders = Order::count();
            $pendingOrders = Order::where('status', 'pending')->count();

            // ✅ Daily revenue (today only)
            $revenue = (float) Order::whereIn('status', ['paid', 'completed'])
                ->whereDate('created_at', Carbon::today())
                ->sum('total');

            // ✅ Low stock items
            $lowStockItems = 0;
            $models = config('components', []);
            if (!empty($models) && is_array($models)) {
                foreach ($models as $modelClass) {
                    if (is_string($modelClass) && class_exists($modelClass)) {
                        try {
                            $lowStockItems += (int) $modelClass::where('stock', '<', 10)->count();
                        } catch (Exception $exModel) {
                            Log::warning("Dashboard: skipped {$modelClass} when calculating low stock: " . $exModel->getMessage());
                            continue;
                        }
                    }
                }
            }

            // ✅ Recent orders
            $recentOrders = Order::with(['user'])->latest()->take(5)->get();
            foreach ($recentOrders as $order) {
                $customerName = 'N/A';
                try {
                    if ($order->relationLoaded('user') && $order->user) {
                        $u = $order->user;
                        $customerName = $u->name ?? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->email ?? 'N/A');
                    }
                } catch (Exception $e) {
                    Log::warning("Dashboard: error resolving customer name for order {$order->id}: " . $e->getMessage());
                }
                $order->customer = (object) ['name' => $customerName];
            }

            // === 📊 Order Volume (Previous Month vs This Month) ===
            $prevMonth = Carbon::now()->subMonth();
            $thisMonth = Carbon::now();

            $previousMonthOrders = Order::whereMonth('created_at', $prevMonth->month)
                ->whereYear('created_at', $prevMonth->year)
                ->count();

            $thisMonthOrders = Order::whereMonth('created_at', $thisMonth->month)
                ->whereYear('created_at', $thisMonth->year)
                ->count();

            return view('admin.dashboard', compact(
                'totalOrders',
                'pendingOrders',
                'revenue',
                'lowStockItems',
                'recentOrders',
                'prevMonth',
                'thisMonth',
                'previousMonthOrders',
                'thisMonthOrders'
            ));
        } catch (Exception $ex) {
            Log::error('Dashboard controller error: ' . $ex->getMessage());

            return view('admin.dashboard', [
                'totalOrders' => 0,
                'pendingOrders' => 0,
                'revenue' => 0,
                'lowStockItems' => 0,
                'recentOrders' => collect(),
                'prevMonth' => Carbon::now()->subMonth(),
                'thisMonth' => Carbon::now(),
                'previousMonthOrders' => 0,
                'thisMonthOrders' => 0,
            ]);
        }
    }
}