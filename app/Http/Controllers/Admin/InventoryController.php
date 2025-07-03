<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\MaterialsImport;
use App\Models\Apptitle;
use App\Models\Footertext;
use App\Models\Material;
use App\Models\MaterialGroup1;
use App\Models\MaterialGroup2;
use App\Models\MaterialGroup3;
use App\Models\Pages;
use App\Models\Seosetting;
use App\Models\Countries;
use App\Models\Timezone;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Inventory;
use App\Exports\InventoryExport;
use App\Exports\InventoryExportTemplate;
use Illuminate\Support\Facades\Validator;
use App\Imports\InventoryImport;

class InventoryController extends Controller
{
    // Display a listing of the resource
    public function index()
    {
        // Fetch all materials
         $data['materials'] = Material::with('group1', 'group2', 'group3')->get();

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;
        // Fetch all inventory items with their related models
        $data['inventory'] = Inventory::allMaterials();

        return view('admin.inventory.index')-> with($data);
    }

    // Show the form with dropdowns
    public function create()
    {
        $timezones = Timezone::get();
        $data['timezones'] = $timezones;

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;


        $data['countries'] = Countries::all();
        $data['materials']= Material::all();

        return view('admin.inventory.create')-> with($data);
    }

    // Store the form data
    public function store(Request $request)
    {
        $request->validate([
            'spare_code' => 'required|string|max:255',
            'spare_name' => 'required|string|max:255',
            'spare_description' => 'nullable|string',
            'country_id' => 'required|exists:countries,id',
            'location_id' => 'required',
            'material_id' => 'required',
            'quantity' => 'required|integer|min:0'
        ]);

        Inventory::create([
            'spare_code' => $request->spare_code,
            'material_id' => $request->material_id,
            'spare_name' => $request->spare_name,
            'spare_description' => $request->spare_description,
            'country_id' => $request->country_id,
            'location_id' => $request->location_id,
            'quantity' => $request->quantity,
            'created_by' => auth()->id(), // Assuming you have user authentication
        ]);
        // Optionally, you can redirect to the index page or show a success message
        // Redirect to the inventory index page with a success message
        // session()->flash('success', 'Inventory created successfully!');
        // or you can use the following line if you want to redirect to a specific route

        return response()->json([
        'message' => 'Inventory created successfully!',
    ]);


        // return redirect('/admin/inventory')->with('success', 'Inventory created successfully!');
    }



    public function showImportForm()
    {
        $data['materials'] = Material::with('group1', 'group2', 'group3')->get();
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;
        return view('admin.material.materialimport')-> with($data);
    }

    public function export()
    {
        return Excel::download(new InventoryExport, 'Inventory-' . now()->format('d-m-Y-h-i-A') . '.xlsx');
    }

    public function downloadtemplate()
    {
        return Excel::download(new InventoryExportTemplate, 'Inventory-Export' . now()->format('d-m-Y-h-i-A') . '.xlsx');
    }

    // public function import(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,csv,xls'
    //     ]);

    //     try {
    //         Excel::import(new InventoryImport, $request->file('file'));
    //         return response()->json(['message' => 'Materials imported successfully!']);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to import materials: ' . $e->getMessage()], 500);
    //     }
    // }


    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv,xls'
    ]);

    try {
        $collection = Excel::toCollection(null, $request->file('file'));

        $rows = $collection[0]; // Get the first sheet

        $errors = [];
        foreach ($rows as $index => $row) {
            // Skip header row
            if ($index === 0) continue;

            $data = [
                'country_id' => $row[0],
                'spare_id' => $row[1],
                'location_id' => $row[2],
                'quantity' => $row[3],
                'spare_code' => $row[4],
                'spare_name' => $row[5],
                'spare_description' => $row[6],
                'country_name' => $row[7],
                'location' => $row[8],
            ];

            $validator = Validator::make($data, [
                'country_id' => 'required|integer',
                'spare_id' => 'required|integer',
                'location_id' => 'required|integer',
                'quantity' => 'required|numeric|min:0',
                'spare_code' => 'required|string',
                'spare_name' => 'required|string',
                'spare_description' => 'nullable|string',
                'country_name' => 'required|string',
                'location' => 'required|string',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $index + 1,
                    'errors' => $validator->errors()->all(),
                ];
            }
        }

        if (!empty($errors)) {
            return response()->json(['validation_errors' => $errors], 422);
        }

        // Now call the import class if needed
        Excel::import(new InventoryImport, $request->file('file'));

        return response()->json(['message' => 'Materials imported successfully!']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to import materials: ' . $e->getMessage()], 500);
    }
}
}
