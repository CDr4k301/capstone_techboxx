<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    //
    public function index() {
        $suppliers = Supplier::paginate(5);

        return view('staff.supplier', compact('suppliers'));
    }

    public function storeSupplier(Request $request) {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        $validate['is_active'] = true;

        Supplier::create($validate);

        return redirect()->route('staff.supplier')->with([
            'message' => 'Supplier added',
            'type' => 'success',
        ]); 
    }

    public function update(Request $request, string $id)  {
        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('staff.supplier')->with([
            'message' => 'Supplier details updated',
            'type' => 'success',
        ]); 
    }

    public function delete($id) {
        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'is_active' => false
        ]);

        return redirect()->route('staff.supplier')->with([
            'message' => 'Supplier status has been inactive',
            'type' => 'success',
        ]);
    }

    public function restore($id) {
        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'is_active' => true
        ]);

        return redirect()->route('staff.supplier')->with([
            'message' => 'Supplier status has been inactive',
            'type' => 'success',
        ]);
    }
}