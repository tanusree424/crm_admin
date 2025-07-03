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
use App\Models\Timezone;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MaterialController extends Controller
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

        return view('admin.material.index')-> with($data);
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

        $data['materialGroups1'] = MaterialGroup1::all();
        $data['materialGroups2'] = MaterialGroup2::all();
        $data['materialGroups3'] = MaterialGroup3::all();

        return view('admin.material.create')-> with($data);
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
