<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\MaterialsImport;
use App\Models\Apptitle;
use App\Models\Footertext;
use App\Models\Material;
use App\Models\Countries;
use App\Models\MaterialGroup1;
use App\Models\MaterialGroup2;
use App\Models\MaterialGroup3;
use App\Models\Pages;
use App\Models\Seosetting;
use App\Models\Timezone;
use App\Models\Location;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LocationController extends Controller
{
    // Display a listing of the resource
    public function index()
    {
    $countries = Countries::get();
    $title = Apptitle::first();
    $footertext = Footertext::first();
    $seopage = Seosetting::first();
    $post = Pages::all();

    $data = [
        'countries' => $countries,
        'title' => $title,
        'footertext' => $footertext,
        'seopage' => $seopage,
        'page' => $post,
    ];

        return view('admin.location.index')-> with($data);
    }

public function create(Request $request)
{
    // Store each field in a variable
    $id = $request->input('id');
    $name = $request->input('name');
    $location = $request->input('location');

        // Check if the location already exists
    $existing = Location::where('country_id', $id)
                        ->where('location', $location)
                        ->first();

    if ($existing) {
        return response()->json(['error' => 'The location already exists for the selected country.'], 409);
    }

    Location::create([
        'country_id' => $id,
        'location' => $location,
    ]);

    // Find Location by Company ID
    $locations = Location::where('country_id', $id)->get();
    if (!$locations) {
        return response()->json(['error' => 'Location not found'], 404);
    }

    return response()->json([
        'message' => 'Form data loaded successfully.',
        'id' => $id,
        'name' => $name,
        'location' => $location,
        'locations' => $locations,
    ]);
}

public function locationByCountryId($countryId)
{
    $locations = Location::where('country_id', $countryId)
        ->where('enabled', 1)
        ->get();

    if ($locations->isEmpty()) {
        return response()->json(['error' => 'No locations found for this country.'], 404);
    }

    return response()->json($locations);
}

// Show the form for creating a new resource
public function createForm()
    {
        $countries = Countries::get();
        $title = Apptitle::first();
        $footertext = Footertext::first();
        $seopage = Seosetting::first();
        $post = Pages::all();

        return view('admin.location.create', compact('countries', 'title', 'footertext', 'seopage', 'post'));
}

public function deleteLocation(Request $request)
{
    $locationId = $request->input('location_id');

    $location = Location::find($locationId);

    if (!$location) {
        return response()->json(['error' => 'Location not found'], 404);
    }

    $location->enabled = 0; // fix field name
    $location->save();

    return response()->json(['success' => 'Location disabled successfully', 'location_id' => $locationId]);
}

    // Store the form data
    public function store(Request $request)
    {
        $request->validate([
            'material_code' => 'required|string|max:255',
            'material_name' => 'required|string|max:255',
            'material_description' => 'nullable|string',
            'mrp' => 'nullable|numeric',
            'division_code' => 'nullable|string',
            'isserialized' => 'nullable|boolean',
            'isrepairable' => 'nullable|boolean',
            'isonsiteallowed' => 'nullable|boolean',
            'is_active' => 'required|boolean',
            'warranty_years' => 'nullable|integer|min:0',
            'warrant_days' => 'nullable|integer|min:0',
            'numberofrepair' => 'nullable|integer|min:0',
            'is_servicecharge_applicable' => 'required|boolean',
        ]);

        Material::create($request->all());

        return redirect('/admin/material')->with('success', 'Material created successfully!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('csv');
        Excel::import(new MaterialsImport, $file);

        return redirect()->route('material.import.form')->with('success', 'Materials imported successfully!');
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
}
