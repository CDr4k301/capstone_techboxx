<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Models\BuildCategory;
use App\Models\Hardware\Cpu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\GoogleDriveUploader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Brand;

class CpuController extends Controller
{
    public function getCpuSpecs()
    {
        return [
            'suppliers' => Supplier::select('id', 'name')->where('is_active', true)->get(),
            'brands' => ['AMD', 'Intel'],
            'socket_types' => ['LGA 1700', 'AM4', 'AM5', ],
            'integrated_displays' => ['Yes', 'No', ],
            'generations' => ['12th Gen', 'Ryzen 5000 Series', '13th Gen', 'Ryzen 7000 Series', ],
            'buildCategories' => BuildCategory::select('id', 'name')->get(),

        ];
    }

    public function getFormattedCpus() 
    {
        $cpus = Cpu::withTrashed()->get();

        $cpuSales = DB::table('user_builds')
                ->select('cpu_id', DB::raw('COUNT(*) as sold_count'))
                ->groupBy('cpu_id')
                ->pluck('sold_count', 'cpu_id');

        $cpus->each(function ($cpu) use ($cpuSales) {
            $cpu->integrated_display = ($cpu->integrated_graphics === 'false') ? 'No' : 'Yes';
            $cpu->price_display = '₱' . number_format($cpu->price, 2);
            $cpu->base_price = $cpu->base_price; // <-- added base_price
            $cpu->label = "{$cpu->brand} {$cpu->model}";
            $cpu->component_type = 'cpu';

            
            $cpu->sold_count = $cpuSales[$cpu->id] ?? 0;
        });

        return $cpus;
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
            'socket_type' => 'required|string|max:255',
            'cores' => 'required|integer|max:255',
            'threads' => 'required|integer|max:255',
            'base_clock' => 'required|numeric',
            'boost_clock' => 'required|numeric',
            'tdp' => 'required|integer|max:255',
            'integrated_graphics' => 'required|string|max:255',
            'generation' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:1|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'model_3d' => 'nullable|file|mimes:glb|max:150000',
            'build_category_id' => 'required|exists:build_categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($validated['image']->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $validated['image']->getClientOriginalExtension();
            $validated['image'] = $validated['image']->storeAs('cpu', $filename, 'public');
        } else {
            $validated['image'] = null;
        }

        // Handle 3D model upload
        if ($request->hasFile('model_3d')) {
            $model3d = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($model3d->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $model3d->getClientOriginalExtension();
            $validated['model_3d'] = $model3d->storeAs('cpu', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }

        // Store base_price
        $validated['base_price'] = $validated['price'];

        // dd($validated);

        Cpu::create($validated);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'CPU added',
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
        // Find the CPU instance
        $cpu = Cpu::findOrFail($id);

        // Prepare data for update
        $data = [
            'build_category_id'   => $request->build_category_id,
            'supplier_id'         => $request->supplier_id,
            'brand'               => $request->brand,
            'model'               => $request->model,
            'socket_type'         => $request->socket_type,
            'cores'               => $request->cores,
            'threads'             => $request->threads,
            'base_clock'          => $request->base_clock,
            'boost_clock'         => $request->boost_clock,
            'tdp'                 => $request->tdp,
            'integrated_graphics' => $request->integrated_graphics,
            'generation'          => $request->generation,
            'price'               => $request->price,
            'base_price'          => $request->price, // <-- added base_price
            'stock'               => $request->stock,
        ];

        // Only update image if new image is uploaded
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('cpu', 'public');
            $data['image'] = $imagePath;
        }

        // Only update model_3d if new file is uploaded
        if ($request->hasFile('model_3d')) {
            $modelPath = $request->file('model_3d')->store('cpu', 'public');
            $data['model_3d'] = $modelPath;
        }

        // Update the CPU with the prepared data
        $cpu->update($data);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'CPU updated',
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
