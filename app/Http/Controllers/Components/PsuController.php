<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Models\BuildCategory;
use App\Models\Hardware\Psu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\GoogleDriveUploader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Brand;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class PsuController extends Controller
{
    // FETCHING DATA FOR DROPDOWNS
    public function getPsuSpecs()
    {
        return [
            'suppliers' => Supplier::select('id', 'name')->where('is_active', true)->get(),
            'brands' => Brand::select('id', 'name', 'supplier_id')->get(),
            'efficiency_ratings' => ['80 PLUS Bronze', '80 PLUS Gold', '80 PLUS Titanium', ],
            'modulars' => ['Non-Modular', 'Semi-Modular', 'Fully Modular', ],
            'buildCategories' => BuildCategory::select('id', 'name')->get(),
        ];
    }

    public function getFormattedPsus()
    {
        $psus = Psu::withTrashed()->get();

        $psuSales = DB::table('user_builds')
            ->select('psu_id', DB::raw('COUNT(*) as sold_count'))
            ->groupBy('psu_id')
            ->unionAll(
                DB::table('cart_items')
                    ->select('product_id as psu_id', DB::raw('SUM(quantity) as sold_count'))
                    ->where('product_type', 'psu') // Only count psu type
                    ->where('processed', 1) // Only count processed cart items
                    ->groupBy('product_id')
            )
            ->get()
            ->groupBy('psu_id')
            ->map(function ($group) {
                return $group->sum('sold_count');
            });

        // FORMATTING THE DATA
        $psus->each(function ($psu) use ($psuSales) {
            $psu->price_display = '₱' . number_format($psu->price, 2);
            $psu->base_price = $psu->base_price; // <-- added base_price
            $psu->label = "{$psu->brand} {$psu->model}";
            $psu->component_type = 'psu';
            $psu->sold_count = $psuSales[$psu->id] ?? 0;
        });

        return $psus;
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
            'wattage' => 'required|integer|max:255',
            'efficiency_rating' => 'required|string|max:255',
            'modular' => 'required|string|max:255',
            'pcie_connectors' => 'required|integer|max:255',
            'sata_connectors' => 'required|integer|max:255',
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
            $validated['image'] = $validated['image']->storeAs('psu', $filename, 'public');
        } else {
            $validated['image'] = null;
        }

        // Handle 3D model upload
        if ($request->hasFile('model_3d')) {
            $model3d = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($model3d->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $model3d->getClientOriginalExtension();
            $validated['model_3d'] = $model3d->storeAs('psu', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }

        // dd($request->all()); 

        $psu = Psu::create($validated);

        ActivityLogService::componentCreated('psu', $psu, $staffUser);
        
        return redirect()->route('staff.componentdetails')->with([
            'message' => 'PSU added',
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
    public function update(Request $request, $id)
    {
        $staffUser = Auth::user();
        $psu = Psu::findOrFail($id);

        $oldPsuData = $psu->toArray();

        // Prepare data for update
        $data = [
            'brand'                 => $request->brand,
            'supplier_id'           => $request->supplier_id,
            'model'                 => $request->model,
            'wattage'               => $request->wattage,
            'efficiency_rating'     => $request->efficiency_rating,
            'modular'               => $request->modular,
            'pcie_connectors'       => $request->pcie_connectors,
            'sata_connectors'       => $request->sata_connectors,
            'price'                 => $request->price,
            'base_price'            => $request->base_price, // <-- added base_price
            'stock'                 => $request->stock,
            'build_category_id'     => $request->build_category_id,
        ];

        // Track file changes
        $fileChanges = [];
        
        // Only update image if a new image is uploaded
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('psu', 'public');
            $data['image'] = $imagePath;
            $fileChanges[] = 'image updated';
        
            ActivityLogService::componentImageUpdated('psu', $psu, $staffUser);
        }

        // Only update model_3d if a new 3D model is uploaded
        if ($request->hasFile('model_3d')) {
            $modelPath = $request->file('model_3d')->store('psu', 'public');
            $data['model_3d'] = $modelPath;
            $fileChanges[] = '3D model updated';
        
            ActivityLogService::component3dModelUpdated('psu', $psu, $staffUser);
        }

        // Update the PSU with the prepared data
        $psu->update($data);

        ActivityLogService::componentUpdated('psu', $psu, $staffUser, $oldPsuData, $psu->fresh()->toArray());
        
        return redirect()->route('staff.componentdetails')->with([
            'message' => 'PSU updated',
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
