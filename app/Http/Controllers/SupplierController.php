<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Supplier;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function search(Request $request)
    {
        $searchTerm = $request->input('search_supplier');

        // Components without search - keep original pagination
        $components = app(ComponentDetailsController::class)->getAllFormattedComponents();
        
        $perPage = 5;
        $currentPage = $request->get('page', 1);
        $currentPageItems = $components->forPage($currentPage, $perPage);

        $paginated = new LengthAwarePaginator(
            $currentPageItems,
            $components->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Suppliers with search functionality
        $suppliers = Supplier::withTrashed()
            ->when($searchTerm, function ($query) use ($searchTerm) {
                return $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('contact_person', 'LIKE', "%{$searchTerm}%");
            })
            ->orderByRaw("CASE WHEN is_active = 0 THEN 1 ELSE 0 END")
            ->orderByDesc('created_at')
            ->paginate(6);

        return view('staff.componentdetails', array_merge(
            [
                'components' => $paginated,
                'suppliers' => $suppliers,
            ],
            app(ComponentDetailsController::class)->getAllSpecs()
        ));
    }
    
    public function storeSupplier(Request $request) {
        $staffUser = Auth::user();
        
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        $validate['is_active'] = true;

        $supplier = Supplier::create($validate);

        // Log the supplier creation
        ActivityLogService::supplierCreated($supplier, $staffUser);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Supplier added',
            'type' => 'success',
        ]); 
    }

    public function update(Request $request, string $id)  {
        $staffUser = Auth::user();
        $supplier = Supplier::findOrFail($id);

        // Store old data for logging
        $oldData = [
            'name' => $supplier->name,
            'contact_person' => $supplier->contact_person,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
        ];

        $supplier->update([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        // Log the supplier update
        ActivityLogService::supplierUpdated($supplier, $staffUser, $oldData, $supplier->fresh()->toArray());

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Supplier details updated',
            'type' => 'success',
        ]); 
    }

    public function delete($id) {
        $staffUser = Auth::user();
        $supplier = Supplier::findOrFail($id);

        // Store old data for logging
        $oldStatus = $supplier->is_active;

        $supplier->update([
            'is_active' => false
        ]);

        // Log the supplier deactivation
        ActivityLogService::supplierDeactivated($supplier, $staffUser, $oldStatus);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Supplier status has been inactive',
            'type' => 'success',
        ]);
    }

    public function restore($id) {
        $staffUser = Auth::user();
        $supplier = Supplier::findOrFail($id);

        // Store old data for logging
        $oldStatus = $supplier->is_active;

        $supplier->update([
            'is_active' => true
        ]);

        // Log the supplier activation
        ActivityLogService::supplierActivated($supplier, $staffUser, $oldStatus);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Supplier status has been activated', // Fixed message
            'type' => 'success',
        ]);
    }
}