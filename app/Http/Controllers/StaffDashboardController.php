<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\OrderedBuild;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StaffDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Orders in progress
        $ordersInProgress = Checkout::whereNull('pickup_status')->count()
                        + OrderedBuild::whereNull('pickup_status')->count();

        // 2. Inventory warnings
        $lowStockThreshold = 10;
        $inventoryWarnings = app(ComponentDetailsController::class)
            ->getAllFormattedComponents()
            ->filter(fn($component) => $component->stock <= $lowStockThreshold)
            ->count();

        // 3. Tasks (combine and paginate)
        $tasks = collect()
            ->merge(Checkout::whereNull('pickup_status')->get())
            ->merge(OrderedBuild::whereNull('pickup_status')->get())
            ->sortByDesc('created_at')
            ->values();

        $tasksPerPage = 10;
        $tasksPage = $request->input('tasks_page', 1);
        $tasksPaginated = new LengthAwarePaginator(
            $tasks->forPage($tasksPage, $tasksPerPage),
            $tasks->count(),
            $tasksPerPage,
            $tasksPage,
            ['path' => $request->url(), 'pageName' => 'tasks_page']
        );

        // 4. Notifications (stock in / stock out)
        $stockIns = StockHistory::where('action', 'stock-in')
            ->latest()
            ->paginate(6, ['*'], 'stockins_page');

        $stockOuts = StockHistory::where('action', 'stock-out')
            ->latest()
            ->paginate(6, ['*'], 'stockouts_page');

        // 5. Fetch all components (brand + model)
        $components = app(\App\Http\Controllers\ComponentDetailsController::class)
            ->getAllFormattedComponents()
            ->mapWithKeys(function ($component) {
                // Handles both id and component_id keys safely
                $key = $component->id ?? $component->component_id ?? null;
                return $key ? [$key => $component] : [];
            });

        // 6. Attach readable product names to stock-ins
        foreach ($stockIns as $item) {
            if (isset($components[$item->component_id])) {
                $component = $components[$item->component_id];
                $item->component_name = trim(($component->brand ?? '') . ' ' . ($component->model ?? ''));
            } else {
                $item->component_name = 'N/A';
            }
        }

        // 7. Attach readable product names to stock-outs
        foreach ($stockOuts as $item) {
            if (isset($components[$item->component_id])) {
                $component = $components[$item->component_id];
                $item->component_name = trim(($component->brand ?? '') . ' ' . ($component->model ?? ''));
            } else {
                $item->component_name = 'N/A';
            }
        }

        // 8. Combine for Blade
        $notifications = [
            'stockIns'  => $stockIns,
            'stockOuts' => $stockOuts,
        ];

        // dd($notifications);
        return view('staff.dashboard', compact(
            'ordersInProgress',
            'inventoryWarnings',
            'tasksPaginated',
            'notifications'
        ));
    }
}
