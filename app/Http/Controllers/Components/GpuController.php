<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Models\BuildCategory;
use App\Models\Hardware\Gpu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\GoogleDriveUploader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Brand;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class GpuController extends Controller
{
    public function getGpuSpecs() 
    {
        return [
            'suppliers' => Supplier::select('id', 'name')->where('is_active', true)->get(),
            'brands' => Brand::select('id', 'name', 'supplier_id')->get(),
            'pcie_interfaces' => ['PCIe 3.0 x16', 'PCIe 4.0 x16', ],
            'connectors_requireds' => ['None', '1 x 8-pin PCIe', '1 x 16-pin PCIe', ],
            'buildCategories' => BuildCategory::select('id', 'name')->get(),
        ];
    }

    public function getFormattedGpus() 
    {
        $gpus = Gpu::withTrashed()->get();

        $gpuSales = DB::table('user_builds')
                ->select('gpu_id', DB::raw('COUNT(*) as sold_count'))
                ->groupBy('gpu_id')
                ->pluck('sold_count', 'gpu_id');
        
        $gpus->each(function ($gpu) use ($gpuSales) {
            $gpu->price_display = '₱' . number_format($gpu->price, 2);
            $gpu->base_price = $gpu->base_price; // <-- added base_price
            $gpu->label = "{$gpu->brand} {$gpu->model}";
            $gpu->component_type = 'gpu';

            
            $gpu->sold_count = $gpuSales[$gpu->id] ?? 0;
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
        $staffUser = Auth::user();

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
            'model_3d' => 'nullable|file|mimes:glb|max:150000',
            'build_category_id' => 'required|exists:build_categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'base_price' => 'required|numeric',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($validated['image']->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $validated['image']->getClientOriginalExtension();
            $validated['image'] = $validated['image']->storeAs('gpu', $filename, 'public');
        } else {
            $validated['image'] = null;
        }

        // Handle 3D model upload
        if ($request->hasFile('model_3d')) {
            $model3d = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($model3d->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $model3d->getClientOriginalExtension();
            $validated['model_3d'] = $model3d->storeAs('gpu', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }

        // Store base_price

        // dd($validated); 

        $gpu = Gpu::create($validated);

        ActivityLogService::componentCreated('gpu', $gpu, $staffUser);

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
        $staffUser = Auth::user();
        $gpu = Gpu::findOrFail($id);

        $oldGpuData = $gpu->toArray();
        
        // Prepare data for update
        $data = [
            'build_category_id'      => $request->build_category_id,
            'supplier_id'            => $request->supplier_id,
            'brand'                  => $request->brand,
            'model'                  => $request->model,
            'vram_gb'                => $request->vram_gb,
            'power_draw_watts'       => $request->power_draw_watts,
            'recommended_psu_watt'   => $request->recommended_psu_watt,
            'length_mm'              => $request->length_mm,
            'pcie_interface'         => $request->pcie_interface,
            'connectors_required'    => $request->connectors_required,
            'price'                  => $request->price,
            'base_price'             => $request->base_price, // <-- added base_price
            'stock'                  => $request->stock,
        ];

        // Track file changes
        $fileChanges = [];

        // Only update image if a new image is uploaded
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('gpu', 'public');
            $data['image'] = $imagePath;
            $fileChanges[] = 'image updated';
        
            ActivityLogService::componentImageUpdated('gpu', $gpu, $staffUser);
        }

        // Only update model_3d if a new 3D model is uploaded
        if ($request->hasFile('model_3d')) {
            $modelPath = $request->file('model_3d')->store('gpu', 'public');
            $data['model_3d'] = $modelPath;
            $fileChanges[] = '3D model updated';
        
            ActivityLogService::component3dModelUpdated('gpu', $gpu, $staffUser);
        }

        // Update the GPU with the prepared data
        $gpu->update($data);

        ActivityLogService::componentUpdated('gpu', $gpu, $staffUser, $oldGpuData, $gpu->fresh()->toArray());
        
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
