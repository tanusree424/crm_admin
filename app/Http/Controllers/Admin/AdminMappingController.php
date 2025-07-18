<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Apptitle;
use App\Models\Footertext;
use App\Models\Seosetting;
use App\Models\Pages;
use App\Models\Mapping;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DataTables;
use App\Imports\MappingImport;
use Maatwebsite\Excel\Facades\Excel;



class AdminMappingController extends Controller
{
// public function index()
// {
//     \Log::info('Inside MappingController@index');

//     try {
//         $data = [
//             'title'      => Apptitle::first(),
//             'footertext' => Footertext::first(),
//             'seopage'    => Seosetting::first(),
//             'page'       => Pages::all(),
//             'mapping'    =>  Mapping::with('user')->get()

//         ];

//         return view('admin.superadmindashboard.Mapping.index', $data);
//     } catch (\Exception $e) {
//         \Log::error('MappingController@index error: ' . $e->getMessage());
//         abort(500, 'Something went wrong');
//     }
// }
public function index()
{
   $data = [
            'title'      => Apptitle::first(),
            'footertext' => Footertext::first(),
            'seopage'    => Seosetting::first(),
            'page'       => Pages::all(),
            'mapping'    =>  Mapping::with('user','customer')->get()

        ];
        return view('admin.superadmindashboard.Mapping.index', $data);
}








public function indexData(Request $request)
{
    if ($request->ajax()) {
        $data = Mapping::with(['customer', 'employee', 'module'])->select('mappings.*');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="sub_chk" data-id="'.$row->id.'">';
            })
            ->addColumn('customer_name', function ($row) {
                return $row->customer->name ?? '';
            })
            ->addColumn('employee_name', function ($row) {
                return $row->employee->name ?? '';
            })
            ->addColumn('module_name', function ($row) {
                return $row->module->name ?? '';
            })
            ->addColumn('status', function ($row) {
                $checked = $row->status == 'active' ? 'checked' : '';
                return '
                    <label class="custom-switch form-switch mb-0">
                        <input type="checkbox" name="status" data-id="' . $row->id . '" class="custom-switch-input mapping-switch" ' . $checked . '>
                        <span class="custom-switch-indicator"></span>
                    </label>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-sm btn-primary editMapping">Edit</a>
                    <a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-sm btn-danger deleteMapping">Delete</a>
                ';
            })
            ->rawColumns(['checkbox', 'status', 'action']) // VERY IMPORTANT
            ->make(true);
    }

   return view('admin.superadmindashboard.Mapping.index');
}

public function edit($id)
{
    $mapping = Mapping::with(['user', 'department', 'customer'])->findOrFail($id);
    $departments = Department::all();
    $customers = Customer::all();

    // Only include users with empid
    $users = User::whereNotNull('empid')->where('empid', '!=', '')->get();

    return response()->json([
        'mapping' => $mapping,
        'departments' => $departments,
        'customers' => $customers,
        'users' => $users
    ]);
}




// Update the mapping
public function update(Request $request, $id)
{
    try {
        // Validate all required fields including empid
        $request->validate([
            'empid'    => 'required|exists:users,id',
            'customer' => 'required|exists:customers,id',
            'modules'  => 'required|string|max:255',
            'status'   => 'required|in:active,inactive',
        ]);

        // Get employee emp_code
        $emp_code = DB::table('users')->where('id', $request->empid)->value('empid');

        // Get customer full name
        $customer_name = DB::table('customers')
            ->where('id', $request->customer)
            ->selectRaw("CONCAT(firstname, ' ', lastname) as fullname")
            ->value('fullname');

        if (!$emp_code || !$customer_name) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user or customer reference.'
            ], 422);
        }

        // Find and update the mapping
        $mapping = Mapping::findOrFail($id);
        $mapping->update([
            'empid'         => $request->empid,
            'emp_code'      => $emp_code,
            'customer'      => $request->customer,
            'customer_name' => $customer_name,
            'modules'       => $request->modules,
            'status'        => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Mapping updated successfully.']);
    } catch (\Exception $e) {
        \Log::error('Mapping update failed: ' . $e->getMessage(), [
            'id' => $id,
            'request_data' => $request->all(),
        ]);
        return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
    }
}




// Delete Mapping
public function destroy($id)
{
    try {
        $mapping = Mapping::findOrFail($id);
        $mapping->delete();

        return response()->json(['success' => true, 'message' => 'Mapping deleted successfully.']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Something went wrong!']);
    }
}
public function toggleStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:active,inactive',
    ]);

    $mapping = Mapping::findOrFail($id);
    $mapping->status = $request->status;
    $mapping->save();

    return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
}

// Mass Delete
public function massDelete(Request $request)
{
    \Log::info('Mass delete request received.', ['ids' => $request->ids]);

    if (!$request->has('ids')) {
        return response()->json(['success' => false, 'message' => 'No IDs provided.']);
    }

    try {
        Mapping::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected mappings deleted successfully.'
        ]);
    } catch (\Exception $e) {
        \Log::error('Mass delete error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error occurred: ' . $e->getMessage()
        ], 500);
    }
}
public function create()
{
    $this->authorize('User Create Mapping Access');

    $title = Apptitle::first();
    $footertext = Footertext::first();
    $seopage = Seosetting::first();
    $pages = Pages::all();
    $departments = Department::all();
    $customers = Customer::all();
    // dd($customers);

    // Fetch only users who have a non-null and non-empty empid
    $user = User::whereNotNull('empid')->where('empid', '!=', '')->get();
    // dd($departments);
    return view('admin.superadmindashboard.Mapping.create', compact(
        'title',
        'footertext',
        'seopage',
        'pages',
        'departments',
        'user',
        'customers'
    ));
}



public function store(Request $request)
{
    $this->authorize('User Create Mapping Access');

    // Log incoming request data
    Log::info("Incoming Request Data: ", $request->all());

    // Validate inputs
    $validator = Validator::make($request->all(), [
        'empid' => 'required|exists:users,id',
        'modules' => 'required|exists:departments,id',
        'customer' => 'required|exists:customers,id',
        'status' => 'nullable|in:on'
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
        // Fetch actual values to store
        $emp = DB::table('users')->where('id', $request->empid)->value('empid');
        $module = DB::table('departments')->where('id', $request->modules)->value('departmentname');
        $customer = DB::table('customers')
            ->where('id', $request->customer)
            ->selectRaw("CONCAT(firstname, ' ', lastname) as fullname")
            ->value('fullname');

        Log::info("Fetched Values: ", [
            'empid' => $request->empid,
            'module' => $module,
            'customer' => $request->customer,
            "emp_code"=>$emp,
            "customer_name"=>$customer
        ]);

        // Validate fetched values
        if (!$emp || !$module || !$customer) {
            Log::warning("Mapping failed due to missing values.", [
            'empid' => $request->empid,
            'modules' => $module,
            'customer' => $request->customer,
            "emp_code"=>$emp,
            "customer_name"=>$customer
            ]);
            return redirect()->back()->with('error', 'Mapping failed due to invalid references.')->withInput();
        }

        // Set status
        $status = $request->has('status') ? 'active' : 'inactive';

        // Insert into DB
       DB::table('usersmapping')->insert([
    'empid' => $request->empid,
    'modules' => $module,
    'customer' => $request->customer,
    'status' => $status,
    'emp_code' => $emp,
    'customer_name' => $customer,
    'created_at' => now(),
    'updated_at' => now()
]);


        return redirect()->route('admin.mapping.index')->with('success', 'Mapping created successfully!');
    } catch (\Exception $e) {
        Log::error("Error inserting user mapping: " . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);
        return redirect()->back()->with('error', 'Something went wrong! Check log for details.')->withInput();
    }
}

public function mappingImportIndex()
{
    \Log::info('Inside mappingImportIndex');

    $title      = Apptitle::first();
    $footertext = Footertext::first();
    $seopage    = Seosetting::first();
    $page       = Pages::all();

    \Log::info('mappingImportIndex Data:', [
        'title' => $title,
        'footertext' => $footertext,
        'seopage' => $seopage,
        'page' => $page,
    ]);

    return view('admin.superadmindashboard.Mapping.import', compact(
        'title', 'footertext', 'seopage', 'page'
    ));
}
public function mappingcsv(Request $request)
{
    \Log::info('Inside mappingcsv() method');

    $this->authorize('Mapping Import');

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        \Log::info('Uploaded file name: ' . $file->getClientOriginalName());

        $path = $file->store('import');
        \Log::info('Stored file path: ' . $path);

        Excel::import(new MappingImport, $path);

        return redirect()->route('admin.mapping.index')->with('success', 'The mapping data was imported successfully.');
    } else {
        \Log::error('No file was uploaded in mappingcsv() method');
        return redirect()->back()->with('error', 'Please select a file to import mapping data.');
    }
}








}



