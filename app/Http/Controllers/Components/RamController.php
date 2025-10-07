<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Models\BuildCategory;
use App\Models\Hardware\Ram;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\GoogleDriveUploader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Brand;

class RamController extends Controller
{
    public function getRamSpecs()
    {
        return [
            'suppliers' => Supplier::select('id', 'name')->where('is_active', true)->get(),
            'brands' => Brand::select('id', 'name', 'supplier_id')->get(),
            'rams' => ['DDR4', 'DDR5'],
            'buildCategories' => BuildCategory::select('id', 'name')->get(),
        ];
    }

    public function getFormattedRams() 
    {
        $rams = Ram::withTrashed()->get();

        $ramSales = DB::table('user_builds')
                ->select('ram_id', DB::raw('COUNT(*) as sold_count'))
                ->groupBy('ram_id')
                ->pluck('sold_count', 'ram_id');

        $rams->each(function ($ram) use ($ramSales) {
            $ram->ecc_display = ($ram->is_ecc === 'false') ? 'No' : 'Yes';
            $ram->rgb_display = ($ram->is_rgb === 'false') ? 'No' : 'Yes';

            $ram->price_display = '₱' . number_format($ram->price, 2);
            $ram->base_price = $ram->base_price; // <-- added base_price
            $ram->label = "{$ram->brand} {$ram->model}";
            $ram->component_type = 'ram';
            $ram->sold_count = $ramSales[$ram->id] ?? 0;
        });

        return $rams;
    }

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'ram_type' => 'required|string|max:255',
            'speed_mhz' => 'required|integer|max:255',
            'size_per_module_gb' => 'required|integer|max:255',
            'total_capacity_gb' => 'required|integer|max:255',
            'module_count' => 'required|integer|max:255',
            'is_ecc' => 'required|string|max:255',
            'is_rgb' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:1|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'model_3d' => 'nullable|file|mimes:glb|max:150000',
            'build_category_id' => 'required|exists:build_categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($validated['image']->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $validated['image']->getClientOriginalExtension();
            $validated['image'] = $validated['image']->storeAs('ram', $filename, 'public');
        } else {
            $validated['image'] = null;
        }

        if ($request->hasFile('model_3d')) {
            $model3d = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($model3d->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $model3d->getClientOriginalExtension();
            $validated['model_3d'] = $model3d->storeAs('ram', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }

        // Store base_price
        $validated['base_price'] = $validated['price'];

        Ram::create($validated);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'RAM added',
            'type' => 'success',
        ]); 
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $ram = Ram::findOrFail($id);

        $data = [
            'build_category_id'    => $request->build_category_id,
            'supplier_id'          => $request->supplier_id,
            'brand'                => $request->brand,
            'model'                => $request->model,
            'ram_type'             => $request->ram_type,
            'speed_mhz'            => $request->speed_mhz,
            'size_per_module_gb'   => $request->size_per_module_gb,
            'total_capacity_gb'    => $request->total_capacity_gb,
            'module_count'         => $request->module_count,
            'is_ecc'               => $request->is_ecc,
            'is_rgb'               => $request->is_rgb,
            'price'                => $request->price,
            'base_price'           => $request->price, // <-- added base_price
            'stock'                => $request->stock,
        ];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('ram', 'public');
            $data['image'] = $imagePath;
        }

        if ($request->hasFile('model_3d')) {
            $modelPath = $request->file('model_3d')->store('ram', 'public');
            $data['model_3d'] = $modelPath;
        }

        $ram->update($data);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'RAM updated',
            'type' => 'success',
        ]);
    }

    public function destroy(string $id)
    {
        //
    }
}
