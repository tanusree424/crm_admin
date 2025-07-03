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
use App\Models\StockTransfer;
use App\Models\Inventory;
use App\Models\TicketProductDertails;
use App\Models\Ticket\Ticket;


class StnController extends Controller
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
        $data['StockTransferList'] = StockTransfer::findAll();

        return view('admin.stn.index')-> with($data);
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


        $data['inventryList']= Inventory::GetInventryList();
         $data['countries'] = Countries::all();

        return view('admin.stn.create')-> with($data);
    }

    // Store the form data
    public function store(Request $request)
    {
        StockTransfer::create([
            'spare_code' => $request->spare_code,
            'material_id' => $request->material_id,
            'spare_description' => $request->spare_description,
            'country_id' => $request->country_id,
            'destination_location_id' => $request->destination_location_id,
            'quantity' => $request->quantity,
            'created_by' => auth()->id(), // Assuming you have user authentication
            'source_location_id' => $request->source_location_id, // New field for source location
            'transfer_status' => 'PENDING', // Default status
            'created_at' => now(), // Set the created_at timestamp
            'transfer_date' => now(), // Set the transfer date to now
            'inventory_id' => $request->inventry_id, // Assuming you have an inventory ID
        ]);
        return response()->json([
        'message' => 'STN created successfully!',
        'body'=> $request
    ]);
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

    public function update(Request $request){

        $stockTransfer = StockTransfer::find($request->stn_id);
        if (!$stockTransfer) {
            return response()->json([
                'message' => 'Stock Transfer not found.'
            ], 404);
        }
        // Update the fields as necessary
        $stockTransfer->transfer_date = $request->transfer_date;
        $stockTransfer->awb_no = $request->awbNo;
        // $stockTransfer->ticker_id = $request->ticker_id;
        $stockTransfer->save();
        // Return a success response    

        // $ticketProductDetails   = TicketProductDertails::where('ticket_id', $stockTransfer->ticket_id)->first();
        // if ($ticketProductDetails) {
        //     $ticketProductDetails->transfer_date = $request->transfer_date;
        //     $ticketProductDetails->awb_no = $request->awbNo;
        //     $ticketProductDetails->save();
        // }

        $ticket = Ticket::where('ticket_id', $stockTransfer->ticket_id)->first();
        if ($ticket) {
            $ticket->transfer_date = $request->transfer_date;
            $ticket->awb_no = $request->awbNo;
            $ticket->save();
        }

        return response()->json([
            'message' => 'Stock Transfer updated successfully!',
            'stockTransfer' => $stockTransfer
        ]);
    }

}
