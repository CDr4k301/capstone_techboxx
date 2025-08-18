<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Models\BuildCategory;
use App\Models\Hardware\Cpu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\GoogleDriveUploader;
use Illuminate\Support\Facades\Config;

class CpuController extends Controller
{
    public function getCpuSpecs()
    {
        return [
            'brands' => ['Intel', 'AMD', ],
            'socket_types' => ['LGA 1700', 'AM4', 'AM5', ],
            'integrated_displays' => ['Yes', 'No', ],
            'generations' => ['12th Gen', 'Ryzen 5000 Series', '13th Gen', 'Ryzen 7000 Series', ],
            'buildCategories' => BuildCategory::select('id', 'name')->get(),

        ];
    }

    public function getFormattedCpus() 
    {
        $cpus = Cpu::all();

        $cpus->each(function ($cpu) {
            $cpu->integrated_display = ($cpu->integrated_graphics === 'false') ? 'No' : 'Yes';
            $cpu->price_display = '₱' . number_format($cpu->price, 2);
            $cpu->component_type = 'cpu';
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
            'image' => 'nullable|array',
            'image.*' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'model_3d' => 'nullable|file|mimes:glb|max:10240',
            'build_category_id' => 'required|exists:build_categories,id',
        ]);

        // Handle image upload
        $uploader = new GoogleDriveUploader();
        $filenames = [];

        if ($request->hasFile('image')) {
            $folderMap = Config::get('googlefolders');
            $type = $request->input('component_type');

            $folderId = $folderMap[$type] ?? Config::get('filesystems.disks.google.folderId');
            // $folderId = '1zm5zcTZCOAMAen1803mWMg1s7r1mcrTj';

            foreach ($request->file('image') as $image) {
                $fileId = $uploader->upload($image, $folderId);
                $filenames[] = $fileId;
            }

            $validated['image'] = $filenames;
        } else {
            $validated['image'] = null;
        }

        // Handle 3D model upload
        if ($request->hasFile('model_3d')) {
            $model3d = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($model3d->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $model3d->getClientOriginalExtension();
            $validated['model_3d'] = $model3d->storeAs('product_3d', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }

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
        //
        $cpu = Cpu::findOrFail($id);

        $cpu->update([
            'build_category_id' => $request->build_category_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'socket_type' => $request->socket_type,
            'cores' => $request->cores,
            'threads' => $request->threads,
            'base_clock' => $request->base_clock,
            'boost_clock' => $request->boost_clock,
            'tdp' => $request->tdp,
            'integrated_graphics' => $request->integrated_graphics,
            'generation' => $request->generation,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

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
