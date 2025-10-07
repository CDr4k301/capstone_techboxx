<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Models\BuildCategory;
use App\Models\Hardware\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage as StorageFacade;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Brand;

class StorageController extends Controller
{
    public function getStorageSpecs()
    {
        return [
            'suppliers' => Supplier::select('id', 'name')->where('is_active', true)->get(),
            'brands' => Brand::select('id', 'name', 'supplier_id')->get(),
            'storage_types' => ['SSD', 'HDD'],
            'interfaces' => ['SATA', 'NVMe'],
            'form_factors' => ['2.5"', '3.5"', 'M.2'],
            'buildCategories' => BuildCategory::select('id', 'name')->get(),
        ];  
    }

    public function getFormattedStorages()
    {
        $storages = Storage::withTrashed()->get();

        $storageSales = DB::table('user_builds')
            ->select('storage_id', DB::raw('COUNT(*) as sold_count'))
            ->groupBy('storage_id')
            ->pluck('sold_count', 'storage_id');

        $storages->each(function ($storage) use ($storageSales) {
            $storage->price_display = '₱' . number_format($storage->price, 2);
            $storage->base_price = $storage->base_price; // <-- added base_price
            $storage->label = "{$storage->brand} {$storage->model}";
            $storage->component_type = 'storage';
            $storage->sold_count = $storageSales[$storage->id] ?? 0;
        });

        return $storages;
    }

    public function index() {}
    public function create() {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function destroy(string $id) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'storage_type' => 'required|string|max:255',
            'interface' => 'required|string|max:255',
            'capacity_gb' => 'required|integer|max:255',
            'form_factor' => 'required|string|max:255',
            'read_speed_mbps' => 'required|integer|max:255',
            'write_speed_mbps' => 'required|integer|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:1|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'model_3d' => 'nullable|file|mimes:glb|max:150000',
            'build_category_id' => 'required|exists:build_categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $validated['image'] = $file->storeAs('storages', $filename, 'public');
        } else {
            $validated['image'] = null;
        }

        if ($request->hasFile('model_3d')) {
            $file = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $validated['model_3d'] = $file->storeAs('storages', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }

        // Store base_price
        $validated['base_price'] = $validated['price'];

        Storage::create($validated);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Storage added',
            'type' => 'success',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $storage = Storage::findOrFail($id);

        $data = [
            'build_category_id' => $request->build_category_id,
            'supplier_id' => $request->supplier_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'storage_type' => $request->storage_type,
            'interface' => $request->interface,
            'capacity_gb' => $request->capacity_gb,
            'form_factor' => $request->form_factor,
            'read_speed_mbps' => $request->read_speed_mbps,
            'write_speed_mbps' => $request->write_speed_mbps,
            'price' => $request->price,
            'base_price' => $request->price, // <-- added base_price
            'stock' => $request->stock,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('storages', 'public');
        }

        if ($request->hasFile('model_3d')) {
            $data['model_3d'] = $request->file('model_3d')->store('storages', 'public');
        }

        $storage->update($data);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Storage updated',
            'type' => 'success',
        ]);
    }
}
