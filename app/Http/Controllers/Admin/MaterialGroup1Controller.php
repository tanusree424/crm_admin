<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialGroup1;
use App\Models\Ticket\Category;
use DataTables;
use App\Models\Apptitle;
use App\Models\Footertext;
use App\Models\Seosetting;
use App\Models\Pages;
use App\Imports\ProjectImport;
use Maatwebsite\Excel\Facades\Excel;
use Auth;
use Str;
use Illuminate\Support\Facades\Validator;
use Response;
use DB;
use App\Models\Projects_category;

class MaterialGroup1Controller extends Controller
{
    public function index()
    {

        $materialgroup1code = MaterialGroup1::latest()->get();
        $data['materialgroup1code'] = $materialgroup1code;

        $basic = Apptitle::first();

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $projects = MaterialGroup1::all();
        $data['project'] = $projects;

        $categories = Category::whereIn('display',['ticket', 'both'])->where('status', '1')
        ->get();
        $data['categories'] = $categories;

        $check_category = Projects_category::pluck('category_id')->toArray();
        $data['check_category'] = $check_category;

        return view('admin.materialgroup1.index',compact('basic','title','footertext'))->with($data)->with('i', (request()->input('page', 1) - 1) * 5);
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',

        ]);

        if($validator->passes()){
            $testiId = $request->material1_id;
            $testi =  [
                'name' => $request->name,
                'description' => $request->description,
            ];

            $project = MaterialGroup1::updateOrCreate(['id' => $testiId], $testi);
            return response()->json(['code'=>200, 'success'=> lang('The project has been updated successfully.', 'alerts'),'data' => $project], 200);

        }else{
            return Response::json(['errors' => $validator->errors()]);
        }
    }

    public function show($id)
    {
        $this->authorize('Project Edit');
        $post = MaterialGroup1::find($id);

        
        return response()->json($post);
    }

    public function destroy($id)
    {

      $testimonial = MaterialGroup1::find($id);
      $testimonial->delete();

      return response()->json(['success'=> lang('The material group 1 successfully deleted.', 'alerts')]);
    }

    public function materialmassdestroy(Request $request){
        $student_id_array = $request->input('id');

        $projects = MaterialGroup1::whereIn('id', $student_id_array)->get();

        foreach($projects as $project){
        $project->delete();
        }
        return response()->json(['success'=> lang('The material group 1 successfully deleted.', 'alerts')]);

    }

    public function projectlist(){

        $category = Category::all();

        $project = MaterialGroup1::all();

        return response()->json(['category'=>$category,'project'=>$project]);
    }

    public function projectassignee(Request $r)
    {
        $this->authorize('Project Assign');
        $projects = MaterialGroup1::find($r->projected);
        if(!empty($projects)){
        foreach ($projects as $project){
            $project->updated_at = now();
            $project->update();

            if($r->input('category_id') == null ){
            $project->projectscategory()->detach($r->input(['category_id']));

            }else{
            foreach ($r->input('category_id') as $value) {
                $category_id[] = $value;
            }
            $project->projectscategory()->sync($r->input(['category_id']));
            }
        }

        return redirect()->back()->with(['success' => lang('The projects were successfully assigned.', 'alerts')]);
        }else{
        return redirect()->back()->with(['error' => lang('Projects have not been assigned.', 'alerts')]);
        }

    }


    public function projetimport(){

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.materialgroup1.projectimport')->with($data);
    }


     /**
    * @return \Illuminate\Support\Collection
    */
    public function projetcsv(Request $req)
    {
        $this->authorize('Project Importlist');
        if ($req->hasFile('file')) {
            $file = $req->file('file')->store('import');

            $import = new ProjectImport;
            $import->import($file);

            return redirect()->route('projects')->with('success', lang('The project list was imported successfully.', 'alerts'));
        }else{
            return redirect()->back()->with('error', 'Please select file to import data of Project.');
        }
    }

    public function notificationpage(){

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $notifications = auth()->user()->notifications()->paginate('10')->groupBy(function($date) {
            return \Carbon\Carbon::parse($date->created_at)->format('Y-m-d');
        });
        $data['notifications'] = $notifications;

        $arraystatus = array('mail');
        $filter =  auth()->user()->notifications()->whereIn('data->status', $arraystatus)->where(function($query){
            $query->where('data->title', 'Sed repellendus in eligendi quo.')
            ->orWhere('data->mailsubject', 'gjfgfgfgfgfghfgghfg');
        })->get();
        $filter1 =  auth()->user()->notifications()->where(function($query){
            $keyword = request()->get('data');
            $query->where('data->title','LIKE', "%{$keyword}%")
            ->orWhere('data->mailsubject','LIKE', "%{$keyword}%")
            ->orWhere('data->mailtext','LIKE', "%{$keyword}%");
        })->get();

        $filter2 =  auth()->user()->notifications()->whereIn('data->status', $arraystatus)->get();

        return view('admin.notificationpage')->with($data);

    }



}
