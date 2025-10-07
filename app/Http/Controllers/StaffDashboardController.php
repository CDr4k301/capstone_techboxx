<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\Order;
use App\Models\OrderedBuild;
use App\Models\StockHistory;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    public function index()
    {
        // 1. Orders in progress
        $ordersInProgress = Checkout::where('pickup_status', null)->count() 
                   + OrderedBuild::where('pickup_status', null)->count();

        // 2. Low stock / inventory warnings
        $lowStockThreshold = 10;
        $inventoryWarnings = app(ComponentDetailsController::class)
            ->getAllFormattedComponents()
            ->filter(fn($component) => $component->stock <= $lowStockThreshold)
            ->count();

        // 3. Tasks (pending orders for approval)
        // $tasks = Checkout::where('pickup_status', 'Pending')->get()
        //            ->merge(OrderedBuild::where('status', 'Pending')->get());

        $tasks = Checkout::where('pickup_status', null)->get()
                   ->merge(OrderedBuild::where('pickup_status', null)->get());

        // 4. Notifications
        $notifications = [
            // Stock movements
            'stockIns'  => StockHistory::where('action', 'stock-in')->latest()->take(5)->get(),
            'stockOuts' => StockHistory::where('action', 'stock-out')->latest()->take(5)->get(),

            // Reorder alerts
            'reorders'  => app(ComponentDetailsController::class)
                ->getAllFormattedComponents()
                ->filter(fn($component) => $component->stock <= 0),
        ];

        return view('staff.dashboard', compact(
            'ordersInProgress',
            'inventoryWarnings',
            'tasks',
            'notifications'
        ));
    }
}