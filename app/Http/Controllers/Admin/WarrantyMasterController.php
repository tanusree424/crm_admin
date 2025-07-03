<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarrantyMasterController extends Controller
{
    public function index()
    {
        $warranties = WarrantyMaster::all();
        return view('warrantymaster.index', compact('warranties'));
    }

    public function create()
    {
        return view('warrantymaster.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'warranty_file' => 'required|file|mimes:csv,txt',
        ]);

        if ($file = $request->file('warranty_file')) {
            $filePath = $file->getRealPath();
            $fileData = array_map('str_getcsv', file($filePath));
            $csvHeader = array_shift($fileData);

            foreach ($fileData as $row) {
                $rowData = array_combine($csvHeader, $row);
                WarrantyMaster::create($rowData);
            }
        }

        return redirect()->route('warrantymaster.index')->with('success', 'Warranties uploaded successfully.');
    }

    public function show($id)
    {
        $warranty = WarrantyMaster::findOrFail($id);
        return view('warrantymaster.show', compact('warranty'));
    }

    public function edit($id)
    {
        $warranty = WarrantyMaster::findOrFail($id);
        return view('warrantymaster.edit', compact('warranty'));
    }

    public function update(Request $request, $id)
    {
        $warranty = WarrantyMaster::findOrFail($id);
        $warranty->update($request->all());
        return redirect()->route('warrantymaster.index')->with('success', 'Warranty updated successfully.');
    }

    public function destroy($id)
    {
        $warranty = WarrantyMaster::findOrFail($id);
        $warranty->delete();
        return redirect()->route('warrantymaster.index')->with('success', 'Warranty deleted successfully.');
    }
}
