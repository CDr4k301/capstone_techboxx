<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Models\BuildCategory;
use App\Models\Hardware\MoboM2Slots;
use App\Models\Hardware\MoboPcieSlot;
use App\Models\Hardware\MoboSataPorts;
use App\Models\Hardware\MoboUsbPorts;
use App\Models\Hardware\Motherboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\GoogleDriveUploader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Brand;
use App\Models\Hardware\Cpu;

class MoboController extends Controller
{
    // FETCHING DATA FOR DROPDOWNS
    public function getMotherboardSpecs()
    {
        return [
            'buildCategories'   => BuildCategory::select('id', 'name')->get(),
            'suppliers'         => Supplier::select('id', 'name')->where('is_active', true)->get(),
            'brands'            => Brand::select('id', 'name', 'supplier_id')->get(),
            'socket_types'      => Cpu::query()->distinct()->pluck('socket_type'),
            'chipsets'          => ['Intel H610', 'AMD B550', 'AMD B450', 'Intel Z790'],
            'form_factors'      => ['E-ATX', 'ATX','Micro-ATX', 'Mini-ITX', ],
            'ram_types'         => ['DDR4', 'DDR5', ],
            'wifi_onboards'     => ['Yes', 'No', ],
            'supported_CPUs'    => Cpu::query()->distinct()->pluck('model'),
        ];
    }

    public function getFormattedMobos()
    {
        $mobos = Motherboard::withTrashed()->get();

        $moboSales = DB::table('user_builds')
                ->select('motherboard_id', DB::raw('COUNT(*) as sold_count'))
                ->groupBy('motherboard_id')
                ->pluck('sold_count', 'motherboard_id');

        // FORMATTING THE DATA
        $mobos->each(function ($mobo) use ($moboSales) {
            if($mobo->wifi_onboard === 'true'){
                $mobo->wifi_display = 'Yes';
            } else {
                $mobo->wifi_display = 'No';
            }
            $mobo->cpu_display = implode('<br>', $mobo->supported_cpu ??[]);

            // Format supported_cpu as an array (for editing)
            $mobo->supported_cpu_array = $mobo->supported_cpu ?? [];
            $mobo->price_display = '₱' . number_format($mobo->price, 2);
            $mobo->base_price = $mobo->base_price; // <-- added base_price
            $mobo->label = "{$mobo->brand} {$mobo->model}";
            $mobo->component_type = 'motherboard';
            
            $mobo->sold_count = $moboSales[$mobo->id] ?? 0;
        });

        return $mobos;
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
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'socket_type' => 'required|string|max:255',
            'chipset' => 'required|string|max:255',
            'form_factor' => 'required|string|max:255',
            'ram_type' => 'required|string|max:255',
            'max_ram' => 'required|integer|max:255',
            'ram_slots' => 'required|string|max:255',
            'max_ram_speed' => 'required|integer|max:255',
            'pcie_slots' => 'required|integer|max:255',
            'm2_slots' => 'required|integer|max:255',
            'sata_ports' => 'required|integer|max:255',
            'usb_ports' => 'required|integer|max:255',
            'wifi_onboard' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:1|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'model_3d' => 'nullable|file|mimes:glb|max:150000',
            'build_category_id' => 'required|exists:build_categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'supported_cpu' => 'nullable|array',
            'supported_cpu.*' => 'nullable|string|max:255'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($validated['image']->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $validated['image']->getClientOriginalExtension();
            $validated['image'] = $validated['image']->storeAs('mobo', $filename, 'public');
        } else {
            $validated['image'] = null;
        }

        // Handle 3D model upload
        if ($request->hasFile('model_3d')) {
            $model3d = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($model3d->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $model3d->getClientOriginalExtension();
            $validated['model_3d'] = $model3d->storeAs('mobo', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }

        // Store base_price
        $validated['base_price'] = $validated['price'];

        Motherboard::create($validated);
    
        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Motherboard added',
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
        // Find the motherboard instance
        $mobo = Motherboard::findOrFail($id);

        // Prepare data for update
        $data = [
            'build_category_id'      => $request->build_category_id,
            'supplier_id'            => $request->supplier_id,
            'brand'                  => $request->brand,
            'model'                  => $request->model,
            'socket_type'            => $request->socket_type,
            'chipset'                => $request->chipset,
            'form_factor'            => $request->form_factor,
            'ram_type'               => $request->ram_type,
            'max_ram'                => $request->max_ram,
            'ram_slots'              => $request->ram_slots,
            'max_ram_speed'          => $request->max_ram_speed,
            'pcie_slots'             => $request->pcie_slots,
            'm2_slots'               => $request->m2_slots,
            'sata_ports'             => $request->sata_ports,
            'usb_ports'              => $request->usb_ports,
            'wifi_onboard'           => $request->wifi_onboard,
            'price'                  => $request->price,
            'base_price'             => $request->price, // <-- added base_price
            'stock'                  => $request->stock,
            'supported_cpu'          => $request->supported_cpu,
        ];

        // Only update image if a new image is uploaded
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('mobo', 'public');
            $data['image'] = $imagePath;
        }

        // Only update model_3d if a new 3D model is uploaded
        if ($request->hasFile('model_3d')) {
            $modelPath = $request->file('model_3d')->store('mobo', 'public');
            $data['model_3d'] = $modelPath;
        }

        // Update the motherboard with the prepared data
        $mobo->update($data);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Motherboard updated',
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
