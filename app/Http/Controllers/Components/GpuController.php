<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Models\BuildCategory;
use App\Models\Hardware\Gpu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\GoogleDriveUploader;
use Illuminate\Support\Facades\Config;

class GpuController extends Controller
{
    public function getGpuSpecs() 
    {
        return [
            'brands' => ['NVIDIA', 'MSI', 'Gigabyte', 'ASUS', ],
            'pcie_interfaces' => ['PCIe 3.0 x16', 'PCIe 4.0 x16', ],
            'connectors_requireds' => ['None', '1 x 8-pin PCIe', '1 x 16-pin PCIe', ],
            'buildCategories' => BuildCategory::select('id', 'name')->get(),
        ];
    }

    public function getFormattedGpus() 
    {
        $gpus = Gpu::all();
        
        $gpus->each(function ($gpu) {
            $gpu->price_display = '₱' . number_format($gpu->price, 2);

            $gpu->component_type = 'gpu';
        });

        return $gpus;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'vram_gb' => 'required|integer|max:255',
            'power_draw_watts' => 'required|integer|min:1|max:450',
            'recommended_psu_watt' => 'required|integer|min:1|max:850',
            'length_mm' => 'required|integer|min:1|max:200',
            'pcie_interface' => 'required|string|max:255',
            'connectors_required' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:1|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'model_3d' => 'nullable|file|mimes:glb|max:10240',
            'build_category_id' => 'required|exists:build_categories,id',
        ]);

        // Handle image upload
        $validated['image'] = $request->file('image');
        $filename = time() . '_' . Str::slug(pathinfo($validated['image']->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $validated['image']->getClientOriginalExtension();
        $validated['image'] = $validated['image']->storeAs('gpu', $filename, 'public');

        // Handle 3D model upload
        if ($request->hasFile('model_3d')) {
            $model3d = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($model3d->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $model3d->getClientOriginalExtension();
            $validated['model_3d'] = $model3d->storeAs('gpu', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }

        // dd($validated); 

        Gpu::create($validated);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'GPU added',
            'type' => 'success',
        ]); 
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $gpu = Gpu::findOrFail($id);

        $gpu->update([
            'build_category_id' => $request->build_category_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'vram_gb' => $request->vram_gb,
            'power_draw_watts' => $request->power_draw_watts,
            'recommended_psu_watt' => $request->recommended_psu_watt,
            'length_mm' => $request->length_mm,
            'pcie_interface' => $request->pcie_interface,
            'connectors_required' => $request->connectors_required,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'GPU updated',
            'type' => 'success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
