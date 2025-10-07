<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BuildCategory;
use App\Models\Hardware\PcCase;
use App\Models\Hardware\PcCaseDriveBay;
use App\Models\Hardware\PcCaseFrontUsbPorts;
use App\Models\Hardware\PcCaseRadiatorSupport;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\GoogleDriveUploader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CaseController extends Controller
{
    public function getCaseSpecs()
    {
        return[
            'suppliers' => Supplier::select('id', 'name')->get(),
            'form_factor_supports' => ['Micro-ATX', 'ATX', 'E-ATX', 'Mini-ITX', ],
            'locations' => ['Front', 'Top', 'Rear', 'Bottom', 'Side'],
            'buildCategories' => BuildCategory::select('id', 'name')->get(),
            
        ];
    }

    public function getFormattedCases()
    {
        $cases = PcCase::withTrashed()->get();
        
        $caseSales = DB::table('user_builds')
                ->select('pc_case_id', DB::raw('COUNT(*) as sold_count'))
                ->groupBy('pc_case_id')
                ->pluck('sold_count', 'pc_case_id');
        $cases->each(function ($case) use ($caseSales) {
            // RADIATOR SUPPORT
            $case->radiator_display = $case->radiatorSupports->groupBy('location')->map(function ($group, $location) {
                $sizes = $group->pluck('size_mm')->unique()->sort()->implode(' / ');
                return ucfirst($location) . ": {$sizes} mm";
            })->implode('<br>');

            // FLATTEN NESTED DATA TO ACCESS FOR THE EDIT FORM
            $driveBay = $case->driveBays->first();
            $case->setAttribute('3_5_bays', optional($driveBay)->{'3_5_bays'} ?? 0);
            $case->setAttribute('2_5_bays', optional($driveBay)->{'2_5_bays'} ?? 0);

            // DRIVE BAYS
            $case->drive_display = $case->driveBays->map(function ($driveBay) {
                return $driveBay->{'3_5_bays'} . ' 3.5" bays' . '<br>' . 
                       $driveBay->{'2_5_bays'} . ' 2.5" bays ';
                
            })->implode('<br>');

            // FLATTEN NESTED DATA TO ACCESS FOR THE EDIT FORM
            $usbPort = $case->usbPorts->first();
            $case->setAttribute('usb_3_0_type_A', optional($usbPort)->{'usb_3_0_type_A'} ?? 0);
            $case->setAttribute('usb_2_0', optional($usbPort)->{'usb_2_0'} ?? 0);
            $case->setAttribute('usb_c', optional($usbPort)->{'usb_c'} ?? 0);
            $case->setAttribute('audio_jacks', optional($usbPort)->{'audio_jacks'} ?? 0);

            // USB PORTS
            $case->usb_display = $case->usbPorts->map(function ($usbPort) {
                return $usbPort->{'usb_3_0_type_A'} . ' USB 3.0 Type-A' . '<br>' . 
                       $usbPort->{'usb_2_0'} . ' USB 2.0' .'<br>' .
                       $usbPort->{'usb_c'} . ' USB-C' .'<br>' .
                       $usbPort->{'audio_jacks'} . ' Audio Jacks';
                
            })->implode('<br>');

            $case->price_display = '₱' . number_format($case->price, 2);
            $case->base_price = $case->base_price; // <-- added base_price variable
            $case->label = "{$case->brand} {$case->model}";
            $case->component_type = 'case';

            
            $case->sold_count = $caseSales[$case->id] ?? 0;
            
        });

        return $cases;
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
            'form_factor_support' => 'required|string|max:255',
            'max_gpu_length_mm'=> 'required|integer|min:1|max:255',
            'max_cooler_height_mm'=> 'required|integer|min:1|max:255',
            'fan_mounts'=> 'required|integer|min:1|max:255',
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
            $validated['image'] = $validated['image']->storeAs('case', $filename, 'public');
        } else {
            $validated['image'] = null;
        }

        // Handle 3D model upload
        if ($request->hasFile('model_3d')) {
            $model3d = $request->file('model_3d');
            $filename = time() . '_' . Str::slug(pathinfo($model3d->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $model3d->getClientOriginalExtension();
            $validated['model_3d'] = $model3d->storeAs('case', $filename, 'public');
        } else {
            $validated['model_3d'] = null;
        }
        // dd($request->all());

        // Store base_price
        $validated['base_price'] = $validated['price'];

        $case = PcCase::create($validated);

        // Validate radiator support
        $request->validate([
            'radiator_support.*.location' => 'required|string|max:255',
            'radiator_support.*.size_mm' => 'required|integer|max:255',
        ]);

        $radiatorSupports = $request->input('radiator_support');
        
        // Store radiator support
        foreach ($radiatorSupports as $radiatorData) {
            PcCaseRadiatorSupport::create([
                'pc_case_id' => $case->id,
                'location' => $radiatorData['location'],
                'size_mm' => $radiatorData['size_mm'],
            ]);
        }

        // Validate drive bays support
        $driveValidated = $request->validate([
            '3_5_bays' => 'required|integer|max:255',
            '2_5_bays' => 'required|integer|max:255',
        ]);
        $driveValidated['pc_case_id'] = $case->id;
        
        PcCaseDriveBay::create($driveValidated);

        // Validate front usb port
        $usbValidated = $request->validate([
            'usb_3_0_type_A' => 'required|integer|max:255',
            'usb_2_0' => 'required|integer|max:255',
            'usb_c' => 'required|integer|max:255',
            'audio_jacks' => 'required|integer|max:255',
        ]);
        $usbValidated['pc_case_id'] = $case->id;
        
        PcCaseFrontUsbPorts::create($usbValidated);

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Case added',
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
        $case = PcCase::findOrFail($id);

        // Prepare data for update
        $data = [
            'build_category_id' => $request->build_category_id,
            'supplier_id' => $request->supplier_id,
            'brand' => $request->brand,
            'model' => $request->model,
            'form_factor_support' => $request->form_factor_support,
            'max_gpu_length_mm' => $request->max_gpu_length_mm,
            'max_cooler_height_mm' => $request->max_cooler_height_mm,
            'fan_mounts' => $request->fan_mounts,
            'price' => $request->price,
            'base_price' => $request->price, // <-- added base_price on update
            'stock' => $request->stock,
        ];

        // Only update image if new image is uploaded
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('cases', 'public');
            $data['image'] = $imagePath;
        }

        // Only update model_3d if new file is uploaded
        if ($request->hasFile('model_3d')) {
            $modelPath = $request->file('model_3d')->store('case', 'public');
            $data['model_3d'] = $modelPath;
        }

        // Update the case with the updated data
        $case->update($data);

        // Delete old supports
        $case->radiatorSupports()->delete();

        // Insert new supports
        foreach ($request->input('radiator_support') as $radiatorData) {
            $case->radiatorSupports()->create([
                'location' => $radiatorData['location'],
                'size_mm' => $radiatorData['size_mm'],
            ]);
        }

        PcCaseDriveBay::updateOrCreate(
            ['pc_case_id' => $case->id],
            [
                '3_5_bays' => $request->input('3_5_bays', 0),
                '2_5_bays' => $request->input('2_5_bays', 0),
            ]
        );

        PcCaseFrontUsbPorts::updateOrCreate(
            ['pc_case_id' => $case->id],
            [
                'usb_3_0_type_A' => $request->input('usb_3_0_type_A', 0),
                'usb_2_0' => $request->input('usb_2_0', 0),
                'usb_c' => $request->input('usb_c', 0),
                'audio_jacks' => $request->input('audio_jacks', 0),
            ]
        );

        return redirect()->route('staff.componentdetails')->with([
            'message' => 'Case updated',
            'type' => 'success',
        ]); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
    }
}
