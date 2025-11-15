<?php

namespace App\Http\Controllers;

use App\Models\Hardware\Cooler;
use App\Models\Hardware\Cpu;
use App\Models\Hardware\Gpu;
use App\Models\Hardware\Motherboard;
use App\Models\Hardware\PcCase;
use App\Models\Hardware\Psu;
use App\Models\Hardware\Ram;
use App\Models\Hardware\Storage;
use App\Models\UserBuild;
use App\Services\CompatibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuildController extends Controller
{
    //
    public function index() {
        $components = app(ComponentDetailsController::class)->getAllFormattedComponents();
        $storages = Storage::get()->map(function ($storage) {
                return (object)[
                    'id' => $storage->id,
                    'component_type' => strtolower($storage->storage_type), // 'hdd' or 'ssd'
                    'brand'          => $storage->brand,
                    'model'          => $storage->model,
                    'label'          => "{$storage->brand} {$storage->model}",
                    'price'          => $storage->price,
                    'image'          => $storage->image,
                    'model_3d'       => $storage->model_3d,
                    'buildCategory'  => $storage->buildCategory,
            ];      
        });

        $components = $components->merge($storages);

        return view('build', compact('components'));
    }

    public function search (Request $request) {
        $searchTerm = strtolower($request->input('search'));

        $components = app(ComponentDetailsController::class)->getAllFormattedComponents()->filter(function ($component) use ($searchTerm) {
            return str_contains(strtolower($component['model']), $searchTerm)
                || str_contains(strtolower($component['brand']), $searchTerm);
        });

        $storages = Storage::get()->map(function ($storage) {
                return (object)[
                    'id' => $storage->id,
                    'component_type' => strtolower($storage->storage_type), // 'hdd' or 'sdd'
                    'brand'          => $storage->brand,
                    'model'          => $storage->model,
                    'label'          => "{$storage->brand} {$storage->model}",
                    'price'          => $storage->price,
                    'image'          => $storage->image,
                    'buildCategory'  => $storage->buildCategory,
            ];      
        })->filter(function ($storage) use ($searchTerm) {
            return str_contains(strtolower($storage->brand), $searchTerm)
                || str_contains(strtolower($storage->model), $searchTerm);
        });

        $components = $components->merge($storages);

        return view('build', compact('components'));
    }

    public function generateBuild(Request $request) {   
        $category = $request->input('category');
        $cpuBrand = $request->input('cpuBrand');
        $userBudget = $request->input('userBudget');

        // Full path to your script
        $scriptPath = base_path('python_scripts/test_python.py');

        // Build the command with python interpreter
        $escapedCategory = escapeshellarg($category);
        $escapedBrand = escapeshellarg($cpuBrand);
        $escapedBudget = escapeshellarg($userBudget);

        $command = escapeshellcmd("python $scriptPath $escapedCategory $escapedBrand $escapedBudget");

        // Execute and capture output + errors
        $output = shell_exec($command . " 2>&1");

        // Debugging: log output if something goes wrong
        Log::info("Python Output: " . $output);

        // Decode JSON safely
        $build = json_decode($output, true);

        if (!$build) {
            return response()->json([
                'error' => 'Python script did not return valid JSON',
                'raw_output' => $output
            ], 500);
        }


        // 🟢 Reformat the build array to match session structure
        $formatted = [];
        foreach ($build as $type => $data) {
            // Skip non-component keys like 'budget_summary'
            if ($type === 'budget_summary') continue;

            // Convert "pc_case" → "case" for consistency
            $cleanType = str_replace('pc_', '', $type);

            $formatted[$cleanType] = [
                'componentId' => $data['id'] ?? null,
                'name'        => $data['name'] ?? '',
                'price'       => $data['price'] ?? 0,
                'imageUrl'    => $data['image'] ?? null,
                'modelUrl'     => $data['model_3d'] ?? null,
            ];
        }

        // 🟢 Store in session
        session(['selected_components' => $formatted]);
        
        return response()->json($build);
    }

    public function validateBuild(Request $request, CompatibilityService $compat) {
        $cpu = Cpu::find($request->cpu_id);
        $mobo = Motherboard::find($request->motherboard_id);
        $gpu = Gpu::find($request->gpu_id);
        $case = PcCase::find($request->case_id);
        $ram = Ram::find($request->ram_id);
        $psu = Psu::find($request->psu_id);
        $cooler = Cooler::find($request->cooler_id);
        $storage = Storage::find($request->storage_id);



        $issues = ['errors' => [], 'warnings' => []];

        if ($cpu && $mobo) {
            $result = $compat->isCpuCompatiblewithMotherboard($cpu, $mobo);
            $issues['errors']   = array_merge($issues['errors'], $result['errors']);
            $issues['warnings'] = array_merge($issues['warnings'], $result['warnings']);
        }
        
        if ($ram && $mobo) {
            $result = $compat->isRamCompatiblewithMotherboard($ram, $mobo);
            $issues['errors']   = array_merge($issues['errors'], $result['errors']);
            $issues['warnings'] = array_merge($issues['warnings'], $result['warnings']);
        }

        if ($gpu && $case) {
            $result = $compat->isGpuCompatiblewithCase($gpu, $case);
            $issues['errors']   = array_merge($issues['errors'], $result['errors']);
            $issues['warnings'] = array_merge($issues['warnings'], $result['warnings']);
        }

        if ($cooler && $mobo && $case) {
            $result = $compat->isCoolerCompatible($cooler, $mobo, $case);
            $issues['errors']   = array_merge($issues['errors'], $result['errors']);
            $issues['warnings'] = array_merge($issues['warnings'], $result['warnings']);
        }

        if($psu && $cpu && $gpu && $cooler){
            $result = $compat->isPsuEnough($psu, $cpu, $gpu,$cooler);
            $issues['errors']   = array_merge($issues['errors'], $result['errors']);
            $issues['warnings'] = array_merge($issues['warnings'], $result['warnings']);
        }

        if ($case && $mobo) {
            $result = $compat->isMotherboardCompatiblewithCase($mobo, $case);
            $issues['errors']   = array_merge($issues['errors'], $result['errors']);
            $issues['warnings'] = array_merge($issues['warnings'], $result['warnings']);
        }

        if ($mobo && $storage) {
            $result = $compat->isStorageCompatiblewithMotherboard($mobo, $storage);
            $issues['errors']   = array_merge($issues['errors'], $result['errors']);
            $issues['warnings'] = array_merge($issues['warnings'], $result['warnings']);
        }

        if (!empty($issues['errors']) || !empty($issues['warnings'])) {
            return response()->json([
                'success'  => empty($issues['errors']), // true if no errors
                'errors'   => $issues['errors'],
                'warnings' => $issues['warnings']
            ]);
        }


        return response()->json(['success' => true]);
    }

    public function saveBuild(Request $request) {
        // Get authenticated user
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to place an order.');
        }
        
        // Validate the main build data
        $validated = $request->validate([
            'build_name' => 'required|string|max:255',
            'total_price' => 'required|numeric|min:0',
        ]);

        // Validate component IDs from the component_ids array
        $componentIds = $request->input('component_ids', []);
        
        // Define required components and their tables
        $requiredComponents = [
            'case' => 'pc_cases',
            'cooler' => 'coolers', 
            'cpu' => 'cpus',
            'gpu' => 'gpus',
            'motherboard' => 'motherboards',
            'psu' => 'psus',
            'ram' => 'rams',
            'storage' => 'storages',
        ];

        // Validate each required component
        foreach ($requiredComponents as $componentType => $tableName) {
            if (!isset($componentIds[$componentType]) || empty($componentIds[$componentType])) {
                return redirect()->back()->with('error', "Missing $componentType component.");
            }
            
            // Check if the component exists in the database
            if (!DB::table($tableName)->where('id', $componentIds[$componentType])->exists()) {
                return redirect()->back()->with('error', "Invalid $componentType selected.");
            }
        }

        try {
            // Create UserBuild record
            UserBuild::create([
                'user_id' => $user->id,
                'build_name' => $validated['build_name'],
                'pc_case_id' => $componentIds['case'],
                'cooler_id' => $componentIds['cooler'],
                'cpu_id' => $componentIds['cpu'],
                'gpu_id' => $componentIds['gpu'],
                'motherboard_id' => $componentIds['motherboard'],
                'psu_id' => $componentIds['psu'],
                'ram_id' => $componentIds['ram'],
                'storage_id' => $componentIds['storage'],
                'total_price' => $validated['total_price'],
                'status' => 'Saved',
            ]);

            return redirect()->route('customer.dashboard')->with([
                'message' => 'Build saved successfully!',
                'type' => 'success',
            ]);

        } catch (\Exception $e) {
            // Log::error('Order build failed: ' . $e->getMessage());
            return redirect()->back()->with('⚠️Error', '⚠️Failed to create order. Please try again.');
        }
    }


    public function storeComponent(Request $request)
    {
        $components = $request->session()->get('selected_components', []);

        $components[$request->type] = [
            'componentId' => $request->componentId,
            'name'        => $request->name,
            'price'       => $request->price,
            'imageUrl'    => $request->imageUrl,
            'modelUrl'     => $request->modelUrl,
        ];

        $request->session()->put('selected_components', $components);

        return response()->json(['message' => 'Component stored successfully']);
    }

    public function updateSession(Request $request)
    {
        $selectedComponents = $request->input('selected_components', []);
        session(['selected_components' => $selectedComponents]);
        return response()->json(['status' => 'success']);
    }


    
}