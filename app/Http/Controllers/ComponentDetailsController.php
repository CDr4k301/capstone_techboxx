<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Components\CaseController;
use App\Http\Controllers\Components\CoolerController;
use App\Http\Controllers\Components\CpuController;
use App\Http\Controllers\Components\GpuController;
use App\Http\Controllers\Components\MoboController;
use App\Http\Controllers\Components\PsuController;
use App\Http\Controllers\Components\RamController;
use App\Http\Controllers\Components\StorageController;
use App\Models\Supplier;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage as FacadesStorage;

class ComponentDetailsController extends Controller
{
    public function getAllFormattedComponents()
    {
        return collect([
            ...app(CpuController::class)->getFormattedCpus(),
            ...app(MoboController::class)->getFormattedMobos(),
            ...app(GpuController::class)->getFormattedGpus(),
            ...app(CaseController::class)->getFormattedCases(),
            ...app(PsuController::class)->getFormattedPsus(),
            ...app(RamController::class)->getFormattedRams(),
            ...app(StorageController::class)->getFormattedStorages(),
            ...app(CoolerController::class)->getFormattedCoolers(),
        ])->sortBy([
                fn ($component) => is_null($component['deleted_at'])  ? 0 : 1,
                fn ($component) => -strtotime($component['created_at']),
            ])
          ->values();
        }

    public function getAllSpecs()
    {
        return [
            'moboSpecs' => app(MoboController::class)->getMotherboardSpecs(),
            'gpuSpecs' => app(GpuController::class)->getGpuSpecs(),
            'caseSpecs' => app(CaseController::class)->getCaseSpecs(),
            'psuSpecs' => app(PsuController::class)->getPsuSpecs(),
            'ramSpecs' => app(RamController::class)->getRamSpecs(),
            'storageSpecs' => app(StorageController::class)->getStorageSpecs(),
            'cpuSpecs' => app(CpuController::class)->getCpuSpecs(),
            'coolerSpecs' => app(CoolerController::class)->getCoolerSpecs(),
        ];
    }

    public function index() {
        $components = $this->getAllFormattedComponents();

        $perPage = 7;
        $currentPage = request()->get('page', 1);
        $currentPageItems = $components->forPage($currentPage, $perPage);

        $paginated = new LengthAwarePaginator(
            $currentPageItems,
            $components->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $suppliers = Supplier::with('brands')
            ->withTrashed()
            ->orderByRaw("CASE WHEN is_active = 0 THEN 1 ELSE 0 END")
            ->orderByDesc('created_at')
            ->paginate(7);

        return view('staff.componentdetails', array_merge(
            [
                'components' => $paginated,
                'suppliers' => $suppliers,
            ],
            $this->getAllSpecs()
        ));
    }

    public function delete(string $type, string $id) {
        $staffUser = Auth::user();
        $modelMap = config('components'); // FOUND IN CONFIG FILE

        if (!array_key_exists($type, $modelMap)) {
            abort(404, "Unknown component type: {$type}");
        }   

        $model = $modelMap[$type];
        $component = $model::findOrFail($id);

        // Store component data for logging before deletion
        $componentData = [
            'id' => $component->id,
            'brand' => $component->brand,
            'model' => $component->model,
            'price' => $component->price,
            'stock' => $component->stock,
            'image' => $component->image,
            'model_3d' => $component->model_3d,
            'build_category_id' => $component->build_category_id,
            'supplier_id' => $component->supplier_id,
        ];

        // DELETE PRODUCT IMAGE
        if ($component->image) {
            FacadesStorage::disk('public')->delete($component->image);
            ActivityLogService::componentImageDeleted($type, $component, $staffUser);
        }

        // DELETE 3D MODEL
        if ($component->model_3d) {
            FacadesStorage::disk('public')->delete($component->model_3d);
            ActivityLogService::component3dModelDeleted($type, $component, $staffUser);
        }

        $component->delete();

        // Log the component deletion
        ActivityLogService::componentDeleted($type, $component, $staffUser, $componentData);

        return back()->with([
            'message' => ucfirst($type) . ' has been deleted.',
            'type' => 'success',
        ]);
    }

    public function restore(string $type, string $id) {
        $staffUser = Auth::user();
        $modelMap = config('components'); // FOUND IN CONFIG FILE

        if (!array_key_exists($type, $modelMap)) {
            abort(404, "Unknown component type: {$type}");
        }   

        $model = $modelMap[$type];
        $component = $model::withTrashed()->findOrFail($id);
        
        // Store component data before restoration
        $componentData = [
            'id' => $component->id,
            'brand' => $component->brand,
            'model' => $component->model,
            'price' => $component->price,
            'stock' => $component->stock,
            'deleted_at' => $component->deleted_at,
        ];

        $component->restore();

        // Log the component restoration
        ActivityLogService::componentRestored($type, $component, $staffUser, $componentData);

        return back()->with([
            'message' => ucfirst($type) . ' has been restored.',
            'type' => 'success',
        ]);
    }

    public function search(Request $request)
    {
        $searchTerm = strtolower($request->input('search'));

        $components = $this->getAllFormattedComponents()->filter(function ($component) use ($searchTerm) {
            return str_contains(strtolower($component['model']), $searchTerm)
                || str_contains(strtolower($component['brand']), $searchTerm);
        });

        // Pagination
        $perPage = 5;
        $currentPage = $request->get('page', 1);
        $currentPageItems = $components->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $currentPageItems,
            $components->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $suppliers = Supplier::orderByRaw("CASE WHEN is_active = 0 THEN 1 ELSE 0 END")
            ->orderByDesc('created_at')
            ->paginate(6);


        return view('staff.componentdetails', array_merge(
            [
                'components' => $paginated,
                'suppliers' => $suppliers,
            ],
            $this->getAllSpecs()
        ));
    }
}
