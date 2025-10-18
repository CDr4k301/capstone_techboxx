<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\Order;
use App\Models\OrderedBuild;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Basic stats
            $totalOrders = Checkout::count() 
                   + OrderedBuild::count();
            $pendingOrders = Checkout::where('pickup_date', null)->count() 
                   + OrderedBuild::where('pickup_date', null)->count();

            // Today's revenue
            $revenue = (float) Checkout::whereNotNull('pickup_date')
                ->whereDate('updated_at', Carbon::today())
                ->sum('total_cost');

            $revenue += (float) OrderedBuild::join('user_builds', 'ordered_builds.user_build_id', '=', 'user_builds.id')
                ->whereNotNull('ordered_builds.pickup_date')
                ->whereDate('ordered_builds.updated_at', Carbon::today())
                ->sum('user_builds.total_price');

            // Low stock items (same logic you had)
            $lowStockItems = 0;
            $models = config('components', []);
            if (!empty($models) && is_array($models)) {
                foreach ($models as $modelClass) {
                    if (is_string($modelClass) && class_exists($modelClass)) {
                        try {
                            $lowStockItems += (int) $modelClass::where('stock', '<', 10)->count();
                        } catch (Exception $exModel) {
                            Log::warning("Dashboard: skipped {$modelClass} when calculating low stock: " . $exModel->getMessage());
                        }
                    }
                }
            }

            // Get recent orders from both sources
            $recentOrders = OrderedBuild::with(['userBuild.user'])->latest()->take(3)->get();
            $recentCheckouts = Checkout::with(['cartItem.shoppingCart.user'])->latest()->take(4)->get();

            // Normalize both collections into a unified structure
            $normalizedOrders = $recentOrders->map(function ($order) {
                return (object) [
                    'id' => $order->id,
                    'type' => 'custom_build',
                    'customer_name' => $this->extractCustomerName($order->userBuild->user ?? null),
                    'date' => $order->created_at,
                    'amount' => $order->userBuild->total_price ?? $order->userBuild->total_price ?? 0,
                    // 'status' => $order->status ?? $order->pickup_status ?? 'unknown',
                    'original' => $order // Keep original if needed
                ];
            });

            $normalizedCheckouts = $recentCheckouts->map(function ($checkout) {
                return (object) [
                    'id' => $checkout->id,
                    'type' => 'checkout',
                    'customer_name' => $this->extractCustomerName($checkout->cartItem->shoppingCart->user ?? null),
                    'date' => $checkout->created_at,
                    'amount' => $checkout->total_cost ?? $checkout->total ?? 0,
                    'status' => $checkout->pickup_status ?? '-',
                    'original' => $checkout
                ];
            });

            // Merge and sort
            $allRecentOrders = $normalizedOrders->toBase()
            ->merge($normalizedCheckouts->toBase())
            ->sortByDesc('date')
            ->take(7);

            // Last 7 days: prepare arrays (plain arrays — easier to json_encode)
            $dates = [];
            $orderCounts = [];
            $revenues = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dates[] = $date->format('M d');

                $orderCounts[] = (int) OrderedBuild::whereDate('created_at', $date)->count()
                    + (int) Checkout::whereDate('created_at', $date)->count();

                $revenues[] = (float) Checkout::whereNotNull('pickup_status')
                    ->whereDate('updated_at', $date)
                    ->sum('total_cost')
                    + (float) OrderedBuild::join('user_builds', 'ordered_builds.user_build_id', '=', 'user_builds.id')
                    ->whereNotNull('ordered_builds.pickup_status')
                    ->whereDate('ordered_builds.updated_at', $date)
                    ->sum('user_builds.total_price');
            }

            return view('admin.dashboard', compact(
                'totalOrders',
                'pendingOrders',
                'revenue',
                'lowStockItems',
                'allRecentOrders',
                'dates',
                'orderCounts',
                'revenues'
            ));
        } catch (Exception $ex) {
            Log::error('Dashboard controller error: ' . $ex->getMessage());

            return view('admin.dashboard', [
                'totalOrders' => 0,
                'pendingOrders' => 0,
                'revenue' => 0,
                'lowStockItems' => 0,
                'allRecentOrders' => collect(),
                'dates' => [],
                'orderCounts' => [],
                'revenues' => [],
            ]);
        }
    }

    private function extractCustomerName($user)
    {
        if (!$user) {
            return 'N/A';
        }
        
        return $user->name 
            ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) 
            ?: $user->email 
            ?? 'N/A';
    }
}