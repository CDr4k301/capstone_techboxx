<?php

namespace App\Http\Controllers;

use App\Models\Hardware\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuildExtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $components = app(ComponentDetailsController::class)->getAllFormattedComponents();
        $storages = Storage::get()->map(function ($storage) {
                $storageSales = DB::table('user_builds')
                ->select('storage_id', DB::raw('COUNT(*) as sold_count'))
                ->where('storage_id', $storage->id)
                ->groupBy('storage_id')
                ->unionAll(
                    DB::table('cart_items')
                        ->select('product_id as storage_id', DB::raw('SUM(quantity) as sold_count'))
                        ->where('product_type', 'storage')
                        ->where('processed', 1)
                        ->where('product_id', $storage->id)
                        ->groupBy('product_id')
                )
                ->get()
                ->sum('sold_count');

                return (object)[
                    'id'              => $storage->id,
                    'component_type'  => strtolower($storage->storage_type), // 'hdd' or 'sdd'
                    'brand'           => $storage->brand,
                    'model'           => $storage->model,
                    'storage_type'    => $storage->storage_type,
                    'interface'       => $storage->interface,
                    'capacity_gb'     => $storage->capacity_gb,
                    'form_factor'     => $storage->form_factor,
                    'read_speed_mbps' => $storage->read_speed_mbps,
                    'write_speed_mbps'=> $storage->write_speed_mbps,
                    'label'           => "{$storage->brand} {$storage->model}",
                    'price'           => $storage->price,
                    'image'           => $storage->image,
                    'model_3d'        => $storage->model_3d,
                    'buildCategory'   => $storage->buildCategory,
                    'sold_count'      => $storageSales,
            ];      
        });

        $components = $components->merge($storages);
        // Retrieve selected components from session
        $selectedComponents = session('selected_components', []);

        return view('buildExt', compact('components', 'selectedComponents'));
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
        //
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
