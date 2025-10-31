<?php

namespace App\Http\Controllers;

use App\Models\StockHistory;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    //
    public function index() {
        $lowStockThreshold = 10;

        // Get all components
        $componentss = app(ComponentDetailsController::class)->getAllFormattedComponents();

        // Count low stock items
        $lowStockCount = $componentss->filter(function ($component) use ($lowStockThreshold) {
            return $component->stock <= $lowStockThreshold;
        })->count();

        // Add status and sort by stock level (lowest first) and then by status
        $componentss->each(function ($component) use ($lowStockThreshold) {
            $component->status = $component->stock <= $lowStockThreshold ? 'Low' : 'Normal';
        });

        // Sort by stock level (ascending) so lowest stock appears first
        $componentss = $componentss->sortBy('stock');

        // Paginate the collection
        $perPage = 7;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $componentss->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $components = new LengthAwarePaginator(
            $items, 
            $componentss->count(), 
            $perPage, 
            $currentPage, 
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view('staff.inventory', compact('components', 'lowStockCount'));
    }


    public function search (Request $request) {
        $lowStockThreshold = 10;
        $searchTerm = strtolower($request->input('search'));

        $componentss = app(ComponentDetailsController::class)->getAllFormattedComponents()->filter(function ($component) use ($searchTerm) {
            return str_contains(strtolower($component['model']), $searchTerm)
                || str_contains(strtolower($component['brand']), $searchTerm);
        });

        $componentss->each(function ($component) use ($lowStockThreshold) {
            $component->status = $component->stock <= $lowStockThreshold ? 'Low' : 'Normal';
        });

        // Sort by stock level (ascending) so lowest stock appears first
        $componentss = $componentss->sortBy('stock');

        // Paginate the collection
        $perPage = 7; // Set the number of items per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $componentss->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $components = new LengthAwarePaginator(
            $items, 
            $componentss->count(), 
            $perPage, 
            $currentPage, 
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view('staff.inventory', array_merge(
            ['components' => $components],
            app(ComponentDetailsController::class)->getAllSpecs()
        ));
    }

    public function stockIn(Request $request) {
        $staffUser = Auth::user();
        
        $validated = $request->validate([
            'label' =>"required|string",
            'type' => "required|string",
            'stockInId' => 'required|integer',
            'stock' => 'required|integer|min:1',
        ]);

        $modelMap = config('components');

        if (!array_key_exists($validated['type'], $modelMap)) {
            abort(404, "Unknown component type: {$validated['type']}");
        }

        $model = $modelMap[$validated['type']];
        $component = $model::findOrFail($validated['stockInId']);

        // Store old stock for logging
        $oldStock = $component->stock;

        // UPDATE THE STOCK
        $component->stock += $validated['stock'];
        $component->save();

        // Create stock history record
        $stockHistory = StockHistory::create([
            'component_id' => $validated['label'],
            'action' => 'stock-in',
            'quantity_changed' => $validated['stock'],
            'user_id' => $staffUser->id,
        ]);

        // Log the stock in action
        ActivityLogService::stockIn($component, $staffUser, $oldStock, $component->stock, $validated['stock']);

        return back()->with([
            'message' => 'Stock successfully added to ' . ucfirst($validated['type']),
            'type' => 'success',
        ]);
    }

    public function stockOut(Request $request) {
        $staffUser = Auth::user();
        
        $validated = $request->validate([
            'label' =>"required|string",
            'type' => "required|string",
            'stockOutId' => 'required|integer',
            'stock' => 'required|integer|min:1',
        ]);

        $modelMap = config('components');

        if (!array_key_exists($validated['type'], $modelMap)) {
            abort(404, "Unknown component type: {$validated['type']}");
        }

        $model = $modelMap[$validated['type']];
        $component = $model::findOrFail($validated['stockOutId']);

        // Check if sufficient stock is available
        if ($component->stock < $validated['stock']) {
            ActivityLogService::stockOutFailed($component, $staffUser, $component->stock, $validated['stock']);
            
            return back()->with([
                'message' => 'Insufficient stock. Available: ' . $component->stock,
                'type' => 'error',
            ]);
        }

        // Store old stock for logging
        $oldStock = $component->stock;

        // UPDATE THE STOCK
        $component->stock -= $validated['stock'];
        $component->save();

        // Create stock history record
        $stockHistory = StockHistory::create([
            'component_id' => $validated['label'],
            'action' => 'stock-out',
            'quantity_changed' => $validated['stock'],
            'user_id' => $staffUser->id,
        ]);

        // Log the stock out action
        ActivityLogService::stockOut($component, $staffUser, $oldStock, $component->stock, $validated['stock']);

        return back()->with([
            'message' => 'Stock successfully removed from ' . ucfirst($validated['type']), // Fixed message
            'type' => 'success',
        ]);
    }
}
