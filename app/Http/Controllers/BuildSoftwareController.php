<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\BuildCategory;
use App\Models\Software;

class BuildSoftwareController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Get the selected component IDs from buildExt.blade
        $selectedIds = $request->input('component_ids', []);

        // 2. Get all components from BuildExtController
        $allComponents = app(BuildExtController::class)->index()->getData()['components'];

        // 3. Filter only the components that were selected
        $selectedComponents = $allComponents->filter(fn($c) => in_array($c->id, $selectedIds))->values();

        // 4. Cache the selected components (full objects) for 30 minutes
        Cache::put('selected_components_full', $selectedComponents, now()->addMinutes(30));

        // 5. Get software & categories
        $softwares = Software::withTrashed()->get();
        $buildCategories = BuildCategory::select('id','name')->get();

        // 6. Return view with everything
        return view('buildExtSoft', [
            'softwares' => $softwares,
            'buildCategories' => $buildCategories,
            'selectedComponents' => $selectedComponents,
        ]);
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
