<?php

namespace App\Http\Controllers;

use App\Models\BuildCategory;
use App\Models\Software;
use App\Models\SoftwareRequirement;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class SoftwareDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $softwares = Software::withTrashed()
            ->orderByRaw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END') // Not deleted first
            ->orderByDesc('created_at')
            ->paginate(7);
        $buildCategories = BuildCategory::select('id', 'name')->get()->toArray();
        return view('staff.softwaredetails', compact('buildCategories', 'softwares'));

    }

    public function search (Request $request) {
        $searchTerm = strtolower($request->input('search'));
        $buildCategories = BuildCategory::select('id', 'name')->get()->toArray();

        $softwares = Software::where('name', 'LIKE', "%{$searchTerm}%")
            ->get();

        // Paginate the collection
        $perPage = 6; // Set the number of items per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $softwares->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $softwares = new LengthAwarePaginator(
            $items, 
            $softwares->count(), 
            $perPage, 
            $currentPage, 
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view('staff.softwaredetails', compact('buildCategories', 'softwares'));
    }

    public function restore(string $id) {
        $staffUser = Auth::user();
        $software = Software::withTrashed()->findOrFail($id);

        // Store software data before restoration
        $softwareData = [
            'id' => $software->id,
            'name' => $software->name,
            'deleted_at' => $software->deleted_at,
        ];

        $software->restore();

        // Log the software restoration
        ActivityLogService::softwareRestored($software, $staffUser, $softwareData);

        return back()->with([
            'message' => 'Software has been restored.',
            'type' => 'success',
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
        $staffUser = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'build_category_id' => 'required|exists:build_categories,id',
            'os_min' => 'nullable|string|max:255',
            'cpu_min' => 'nullable|string|max:255',
            'gpu_min' => 'nullable|string|max:255',
            'ram_min' => 'nullable|integer',
            'storage_min' => 'nullable|integer',
            'cpu_reco' => 'nullable|string|max:255',
            'gpu_reco' => 'nullable|string|max:255',
            'ram_reco' => 'nullable|integer',
            'storage_reco' => 'nullable|integer',
        ]);

        // Handle image upload
        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon');
            $filename = time() . '_' . Str::slug(pathinfo($validated['icon']->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $validated['icon']->getClientOriginalExtension();
            $validated['icon'] = $validated['icon']->storeAs('softwareIcon', $filename, 'public');
            
            // Log icon upload
            ActivityLogService::softwareIconUploaded(null, $staffUser, $filename);
        } else {
            $validated['icon'] = null;
        }

        $software = Software::create($validated);

        // Log the software creation
        ActivityLogService::softwareCreated($software, $staffUser);

        return redirect()->route('staff.software-details')->with([
            'message' => 'Software added',
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
        $software = Software::findOrFail($id);

        // Store old data for logging
        $oldData = $software->toArray();

        $data = [
            'name' => $request->name,
            'build_category_id' => $request->build_category_id,
            'os_min' => $request->input('os_min') ?: $software->os_min,     
            'cpu_min' => $request->input('cpu_min') ?: $software->cpu_min,     
            'gpu_min' => $request->input('gpu_min') ?: $software->gpu_min,     
            'ram_min' => $request->input('ram_min') ?: $software->ram_min,     
            'storage_min' => $request->input('storage_min') ?: $software->storage_min,     
            'cpu_reco' => $request->input('cpu_reco') ?: $software->cpu_reco,     
            'gpu_reco' => $request->input('gpu_reco') ?: $software->gpu_reco,     
            'ram_reco' => $request->input('ram_reco') ?: $software->ram_reco,     
            'storage_reco' => $request->input('storage_reco') ?: $software->storage_reco,  
        ];

        // Handle icon update
        if ($request->hasFile('icon')) {
            $imagePath = $request->file('icon')->store('softwareIcon', 'public');
            $data['icon'] = $imagePath;
            
            // Log icon update
            ActivityLogService::softwareIconUpdated($software, $staffUser, $imagePath);
        } else {
            $data['icon'] = $software->icon; // Keep existing icon
        }

        $software->update($data);

        // Log the software update
        ActivityLogService::softwareUpdated($software, $staffUser, $oldData, $software->fresh()->toArray());

        return redirect()->route('staff.software-details')->with([
            'message' => 'Software updated',
            'type' => 'success',
        ]); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $staffUser = Auth::user();
        $software = Software::findOrFail($id);

        // Store software data for logging before deletion
        $softwareData = $software->toArray();

        if ($software->icon) {
            Storage::disk('public')->delete($software->icon);
            // Log icon deletion
            ActivityLogService::softwareIconDeleted($software, $staffUser);
        }

        $software->delete();

        // Log the software deletion
        ActivityLogService::softwareDeleted($software, $staffUser, $softwareData);

        return redirect()->route('staff.software-details')->with([
            'message' => 'Software deleted',
            'type' => 'success',
        ]); 
    }
}
