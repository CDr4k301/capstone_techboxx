<?php

namespace App\Http\Controllers\Components;
 
use App\Http\Controllers\Controller;
use App\Models\BuildCategory;
use App\Models\Hardware\Cooler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Supplier;
use App\Models\Brand;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class CoolerController extends Controller
{
    //
    public function getCoolerSpecs()
    {
        return[
            'suppliers' => Supplier::select('id', 'name')->where('is_active', true)->get(),
            'cooler_types' => ['Air Cooler', 'Liquid Cooler'],
            'socket_compatibilities' => ['LGA 1700', 'AM5', 'AM4'],
            'buildCategories' => BuildCategory::select('id', 'name')->get(),
        ];
    }

    public function getFormattedCoolers()
    {
        $coolers = Cooler::withTrashed()->get();

        $coolerSales = DB::table('user_builds')
            ->select('cooler_id', DB::raw('COUNT(*) as sold_count'))
            ->groupBy('cooler_id')
            ->unionAll(
                DB::table('cart_items')
                    ->select('product_id as cooler_id', DB::raw('SUM(quantity) as sold_count'))
                    ->where('product_type', 'cooler') // Only count cooler type
                    ->where('processed', 1) // Only count processed cart items
                    ->groupBy('product_id')
            )
            ->get()
            ->groupBy('cooler_id')
            ->map(function ($group) {
                return $group->sum('sold_count');
            });

        $coolers->each(function ($cooler) use ($coolerSales) {
            $cooler->socket_display = implode('<br>', $cooler->socket_compatibility ?? []);

            // Format socket_compatibility as an array (for editing)
            $cooler->socket_compatibility_array = $cooler->socket_compatibility ?? [];
            $cooler->label = "{$cooler->brand} {$cooler->model}";
            $cooler->price_display = '₱' . number_format($cooler->price, 2);
            $cooler->base_price = $cooler->base_price; // <-- added base_price
            $cooler->component_type = 'cooler';

            
            $cooler->sold_count = $coolerSales[$cooler->id] ?? 0;
        });
        return $coolers;
    }

    public function store(Request $request)
    {
        $staffUser = Auth::user();

        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'cooler_type' => 'required|string|max:255',
            'socket_compatibility' => 'required|array',
            'socket_compatibility.*' => 'required|string|max:255',
            'max_tdp' => 'required|integer|min:1',
            'radiator_size_mm' => 'nullable|integer|min:1',
            'fan_count' => 'required|integer|min:1',
            'height_mm' => 'required|integer|min:1',
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
            $validated['image'] = $validated['image']->storeAs('cooler', $filename, 'public');
        } else {
            $validated['image'] = null;
        }


        // Handle 3D model upload
        if ($request->hasFile('model_3d')) {
            $model3d = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($model3d->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $model3d->getClientOriginalExtension();
            $validated['model_3d'] = $model3d->storeAs('cooler', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }

        // Store base_price

        $cooler = Cooler::create($validated);

        ActivityLogService::componentCreated('cooler', $cooler, $staffUser);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Cooler added',
            'type' => 'success',
        ]); 
    }

    public function update(Request $request, string $id) 
    {
        $staffUser = Auth::user();
        $cooler = Cooler::findOrFail($id);

        $oldCoolerData = $cooler->toArray();

        // Prepare data for update
        $data = [
            'brand'                => $request->brand, 
            'supplier_id'          => $request->supplier_id,
            'model'                => $request->model,
            'cooler_type'          => $request->cooler_type,
            'socket_compatibility' => $request->socket_compatibility,
            'max_tdp'              => $request->max_tdp,
            'radiator_size_mm'     => $request->radiator_size_mm,
            'fan_count'            => $request->fan_count,
            'height_mm'            => $request->height_mm,
            'price'                => $request->price,
            'base_price'           => $request->base_price, // <-- added base_price on update
            'stock'                => $request->stock,
        ];

        // Track file changes
        $fileChanges = [];

        // Only update image if new image is uploaded
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('cooler', 'public');
            $data['image'] = $imagePath;
            $fileChanges[] = 'image updated';
        
            ActivityLogService::componentImageUpdated('cooler', $cooler, $staffUser);
        }

        // Only update model_3d if new file is uploaded
        if ($request->hasFile('model_3d')) {
            $modelPath = $request->file('model_3d')->store('cooler', 'public');
            $data['model_3d'] = $modelPath;
            $fileChanges[] = '3D model updated';
        
            ActivityLogService::component3dModelUpdated('cooler', $cooler, $staffUser);
        }

        // Update the cooler with the prepared data
        $cooler->update($data);

        ActivityLogService::componentUpdated('cooler', $cooler, $staffUser, $oldCoolerData, $cooler->fresh()->toArray());
        
        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Cooler updated',
            'type' => 'success',
        ]); 
    }
}
