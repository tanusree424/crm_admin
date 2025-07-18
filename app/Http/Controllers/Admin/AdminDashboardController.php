<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Ticket\Ticket;
use Auth;
use App\Models\User;
use App\Models\usersettings;
use App\Models\Apptitle;
use App\Models\Footertext;
use App\Models\Seosetting;
use App\Models\Pages;
use App\Models\Customer;
use App\Models\Groupsusers;
use App\Models\Groups;
use App\Models\Ticket\Category;
use DB;
use DataTables;
use Carbon\Carbon;
use Session;
use Illuminate\Support\Str;
use Artisan;
use App\Models\ticketassignchild;
use Illuminate\Support\Facades\Mail;


use App\Mail\FollowupMail;


class AdminDashboardController extends Controller
{



    public function index()
    {
        if(Auth::user()->dashboard == 'Admin'){
            return $this->adminDashboard();
        }
        if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            return $this->Dashboard();
        }

    }

    //Super Admin Dashboard
    public function adminDashboard()
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        // Ticket Counting
        $totaltickets = Ticket::count();
        $data['totaltickets'] = $totaltickets;

        $totalactivetickets = Ticket::whereIn('status',['Re-Open','Inprogress','On-Hold'])->count();
        $data['totalactivetickets'] = $totalactivetickets;

        $totalclosedtickets = Ticket::where('status','Closed')->count();
        $data['totalclosedtickets'] = $totalclosedtickets;

        $replyrecent = Ticket::whereIn('status',['Re-Open','Inprogress','On-Hold'])->where('replystatus', 'Replied')->count();
        $data['replyrecent'] = $replyrecent;

        $recentticketlist = Ticket::where('status','New')->get();
        $recentticketcount = 0;

        foreach($recentticketlist as $recent){
            if($recent->myassignuser_id == null && $recent->selfassignuser_id == null && $recent->toassignuser_id == null){
                $recentticketcount += 1;
            }
        }
        $data['recentticketcount'] = $recentticketcount;

        $selfassigncount = Ticket::where('selfassignuser_id',Auth::id())->where('status', '!=' ,'Closed')->where('status', '!=' ,'Suspend')->count();
        $data['selfassigncount'] = $selfassigncount;

        $myassignedticket = Ticket::leftJoin('ticketassignchildren','ticketassignchildren.ticket_id','tickets.id')->where('toassignuser_id',Auth::id())->where('status', '!=' ,'Closed')->where('status', '!=' ,'Suspend')->get();
        $myassignedticketcount = 0;
        foreach($myassignedticket as $recent){
            if( $recent->toassignuser_id != null){
                $myassignedticketcount += 1;
            }
        }
        $data['myassignedticketcount'] = $myassignedticketcount;


        $myclosedticketcount = Ticket::where('closedby_user',Auth::id())->count();
        $data['myclosedticketcount'] = $myclosedticketcount;

        $suspendedticketcount = Ticket::where('status','Suspend')->count();
        $data['suspendedticketcount'] = $suspendedticketcount;

        $alltickets = Ticket::whereIn('status', ['New'])->latest('updated_at')->get();
        $data['alltickets'] = $alltickets;

        $data['gtickets'] = Ticket::whereIn('status', ['New'])->latest('updated_at')->get();

        $suspendticketcount = Ticket::where('status', 'Suspend')->where('lastreply_mail',Auth::id())->count();
        $data['suspendticketcount'] = $suspendticketcount;


        return view('admin.superadmindashboard.dashboard')->with($data);
    }

    // Employee Dashboard
    public function Dashboard()
    {
        $groups =  Groups::where('groupstatus', '1')->get();

        $group_id = '';
        foreach($groups as $group){
            $group_id .= $group->id . ',';
        }


        $groupexists = Groupsusers::whereIn('groups_id', explode(',', substr($group_id,0,-1)))->where('users_id', Auth::id())->exists();

        // if there in group get group tickets
        if($groupexists){

            if(Auth::user()->dashboard == 'Admin'){
            $totalactivetickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.status', ['Inprogress', 'Re-Open', 'On-Hold'])
            ->where('status', '!=' ,'Closed')
            ->whereNotNull('groups_users.users_id')
            ->whereNull('tickets.myassignuser_id')
            ->whereNull('tickets.selfassignuser_id')
            ->where('groups_users.users_id', Auth::id())
            ->count();
            }else{
                $totalactivetickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.status', ['Inprogress', 'Re-Open', 'On-Hold'])
                ->where('user_id', Auth::id())
                ->where('status', '!=' ,'Closed')
                ->whereNotNull('groups_users.users_id')
                ->whereNull('tickets.myassignuser_id')
                ->whereNull('tickets.selfassignuser_id')
                ->where('groups_users.users_id', Auth::id())
                ->count();
            }
            $data['totalactivetickets'] = $totalactivetickets;

            $totalactiverecent = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.status', ['Inprogress', 'Re-Open', 'On-Hold'])
            ->where('replystatus', 'Replied')
            ->whereNotNull('groups_users.users_id')
            ->whereNull('tickets.myassignuser_id')
            ->whereNull('tickets.selfassignuser_id')
            ->where('groups_users.users_id', Auth::id())
            ->count();
            $data['totalactiverecent'] = $totalactiverecent;

            if(Auth::user()->dashboard == 'Admin'){
            $recentticketcount = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->where('tickets.status','New')
            ->whereNull('tickets.myassignuser_id')
            ->whereNull('tickets.selfassignuser_id')
            ->whereNotNull('groups_users.users_id')
            ->where('groups_users.users_id', Auth::id())
            ->count();
            }else{
                $recentticketcount = Ticket::where('selfassignuser_id',Auth::id())->orWhere('myassignuser_id',Auth::id())->orWhere('user_id',Auth::id())->count();
            }
            $data['recentticketcount'] = $recentticketcount;

            $gticket = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "groups.*")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->leftJoin('groups','groups.id','groups_users.groups_id')
                ->whereNotNull('groups_users.users_id')
                ->where('groups.groupstatus', '1')
                ->where('groups_users.users_id', Auth::id())
                ->get();
            $data['gtickets'] = $gticket;


            $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
            $data['ticketnote'] = $ticketnote;


        }
        // If no there in group we get the all tickets
        else{

            if(Auth::user()->dashboard == 'Admin'){
            $totalactivetickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.status', ['Re-Open','Inprogress','On-Hold'])
            ->where('status', '!=' ,'Closed')
            ->whereNull('tickets.myassignuser_id')
            ->whereNull('tickets.selfassignuser_id')
            ->whereNull('groups_users.users_id')
            ->count();
            }else{
                $totalactivetickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.status', ['Re-Open','Inprogress','On-Hold'])
                ->where('user_id', Auth::id())
                ->where('status', '!=' ,'Closed')
                ->whereNull('tickets.myassignuser_id')
                ->whereNull('tickets.selfassignuser_id')
                ->whereNull('groups_users.users_id')
                ->count();
            }
            $data['totalactivetickets'] = $totalactivetickets;

            $totalactiverecent = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.status', ['Re-Open','Inprogress','On-Hold'])
            ->where('replystatus', 'Replied')
            ->whereNull('tickets.myassignuser_id')
            ->whereNull('tickets.selfassignuser_id')
            ->whereNull('groups_users.users_id')
            ->count();
            $data['totalactiverecent'] = $totalactiverecent;

            if(Auth::user()->dashboard == 'Admin'){
            $recentticketcount = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->where('status','New')
            ->whereNull('tickets.myassignuser_id')
            ->whereNull('tickets.selfassignuser_id')
            ->whereNull('groups_users.users_id')->count();
            }else{
                $recentticketcount = Ticket::where('selfassignuser_id',Auth::id())->orWhere('myassignuser_id',Auth::id())->orWhere('user_id',Auth::id())->count();
            }
            $data['recentticketcount'] = $recentticketcount;



            $gtickets = Ticket::latest('updated_at')->get();
            $data['gtickets'] = $gtickets;

            $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
            $data['ticketnote'] = $ticketnote;

        }

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $selfassigncount = Ticket::where('selfassignuser_id',Auth::id())->where('status', '!=' ,'Closed')->where('status', '!=' ,'Suspend')->count();
        $data['selfassigncount'] = $selfassigncount;

        $selfassignrecentreply = Ticket::where('selfassignuser_id',Auth::id())->where('replystatus', 'Replied')->where('status', '!=' ,'Closed')->count();
        $data['selfassignrecentreply'] = $selfassignrecentreply;

        $myassignedticketcount = Ticket::leftJoin('ticketassignchildren','ticketassignchildren.ticket_id','tickets.id')->where('toassignuser_id',Auth::id())->where('status', '!=' ,'Closed')->where('status', '!=' ,'Suspend')->count();
        $data['myassignedticketcount'] = $myassignedticketcount;

        $myassignedticketrecentreply = Ticket::leftJoin('ticketassignchildren','ticketassignchildren.ticket_id','tickets.id')->where('toassignuser_id',Auth::id())->where('status', '!=' ,'Closed')->where('replystatus', 'Replied')->count();
        $data['myassignedticketrecentreply'] = $myassignedticketrecentreply;

        $myclosedticketcount = Ticket::where('closedby_user',Auth::id())->count();
        $data['myclosedticketcount'] = $myclosedticketcount;

        $suspendticketcount = Ticket::where('status', 'Suspend')->where('lastreply_mail',Auth::id())->count();
        $data['suspendticketcount'] = $suspendticketcount;

        return view('admin.dashboard')->with($data);

    }

    public function dashboardtabledata()
    {

        if(Auth::user()->dashboard == 'Admin'){
            return $this->adminDashboardtabledata();
        }
        if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            return $this->Dashboardtabledatas();
        }

        // return auth()->user()->hasRole('superadmin') ? $this->adminDashboardtabledata() : $this->Dashboardtabledatas();

    }

    public function getData(Request $request)
    {
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('customers','customers.id','tickets.cust_id')
                ->leftJoin('ticket_customfields', function ($join) {
                    $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                        ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
                })
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.status', ['New'])
                ->whereNull('tickets.myassignuser_id')
                ->whereNull('tickets.selfassignuser_id')
                ->whereNull('groups_users.users_id')
                ->latest('tickets.updated_at');
            // For searching Accoring to value 11-07-2025
                    if ($request->has('search') && !empty($request->search['value'])) {
                    $searchValue = '%' . $request->search['value'] . '%';

                    $query->where(function ($q) use ($searchValue) {
                        $q->where('tickets.subject', 'like', $searchValue)
                        ->orWhere('ticket_customfields.values', 'like', $searchValue) // ✅ this is mobile
                        ->orWhere('tickets.ticket_id', 'like', $searchValue)
                        ->orWhere('customers.username', 'like', $searchValue)
                        ->orWhere('customers.firstname', 'like', $searchValue)
                        ->orWhere('customers.lastname', 'like', $searchValue);
                    });
                    }
        // End

                $query->get();

                return DataTables::of($query)
                    ->addColumn('serial', function ($row) {
                        return '';
                    })
                    ->addColumn('id', function ($row) {
                        $html = '<a href="admin/ticket-view/' . $row->ticket_id .'" class="fs-14 d-block font-weight-semibold">' .$row->subject . '</a>
                        <ul class="fs-13 font-weight-normal d-flex custom-ul">
                            <li class="pe-2 text-muted">#' . $row->ticket_id .'</span>
                            <li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Date').'"><i class="fe fe-calendar me-1 fs-14"></i> '.$row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')).'</li>';

                            if($row->priority != null)
                                if($row->priority == "Low")
                                    $html .= '<li class="ps-5 pe-2 preference preference-low" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                                elseif($row->priority == "High")
                                    $html .= '<li class="ps-5 pe-2 preference preference-high" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                                elseif($row->priority == "Critical")
                                    $html .= '<li class="ps-5 pe-2 preference preference-critical" data-bs-toggle="tooltip" data-bs-placement="top" title="' .lang('Priority') . '"> '.lang($row->priority) . '</li>';

                                else
                                    $html .= '<li class="ps-5 pe-2 preference preference-medium" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">' .lang($row->priority) .'</li>';
                            else
                                $html .= '~';

                            if($row->category_id != null)
                                if($row->category != null)
                                    $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.Str::limit($row->category->name, '40') .'</li>';
                                else
                                    $html .= '~';
                            else
                                $html .= '~';

                            if($row->last_reply == null)
                                $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'. lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' . $row->created_at->diffForHumans() .'</li>';
                            else
                                $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' .$row->last_reply->diffForHumans() .'</li>';

                            if($row->purchasecodesupport != null)
                                if($row->purchasecodesupport == 'Supported')
                                    $html .= '<li class="px-2 text-success font-weight-semibold">' . lang('Support Active') .'</li>';

                                if($row->purchasecodesupport == 'Expired')
                                    $html .= '<li class="px-2 text-danger-dark font-weight-semibold">' .lang('Support Expired') .'</li>';

                        $html .= '</ul>';
                        return $html;
                    })
                    ->addColumn('custname', function ($row) {
                        return $row->cust->username . ' (' . lang($row->cust->userType) . ')';
                    })
                    ->addColumn('mobilenumber', function ($row) {
                        $mobileNo = $row->values;
                        return $mobileNo;
                    })
                    ->addColumn('status', function ($row) {
                        $status = '';
                        if($row->status == "New")
                            $status = '<span class="badge badge-burnt-orange">' .lang($row->status) .'</span>';

                        elseif($row->status == "Re-Open")
                            $status = '<span class="badge badge-teal">' . lang($row->status) .'</span>';

                        elseif($row->status == "Inprogress")
                            $status = '<span class="badge badge-info">' . lang($row->status) .'</span>';

                        elseif($row->status == "On-Hold")
                            $status = '<span class="badge badge-warning">' . lang($row->status) .'</span>';

                        else
                            $status = '<span class="badge badge-danger">' . lang($row->status) .'</span>';

                        return $status;
                    })
                    ->addColumn('assignedTo', function ($row) {
                        $assignedTo = '';
                        if(Auth::user()->can('Ticket Assign')){
                            if($row->status == 'Suspend' || $row->status == 'Closed'){
                                $assignedTo .= '<div class="btn-group">';
                                    if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" disabled>'.lang('Multi Assign') .' <span class="caret"></span></button>';
                                        $assignedTo .= '<button data-id="' .$row->id .'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                                    }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>{{$row->selfassign->name}} (self) <span class="caret"></span></button>';
                                        $assignedTo .= '<button data-id="'.$row->id.'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign').'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>'.lang('Assign').'<span class="caret"></span></button>';
                                    }
                                $assignedTo .= '</div>';
                            }else{
                                if($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id == null){
                                    $assignedTo .= '<div class="btn-group">';
                                    $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign').' <span class="caret"></span></button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li class="dropdown-plus-title">'.lang('Assign').' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                            <li>
                                                <a href="javascript:void(0);" id="selfassigid" data-id="'.$row->id.'">'.lang('Self Assign').'</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                                '.lang('Other Assign').'
                                                </a>
                                            </li>
                                        </ul>
                                    </div>';
                                }else{
                                    $assignedTo .= '<div class="btn-group">';
                                        if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                            if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .'<span class="caret"></span></button>';
                                            }else{
                                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Multi Assign') .'<span class="caret"></span></button>';
                                                $assignedTo .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                            }
                                        }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                            if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .' <span class="caret"></span></button>';
                                            }else{
                                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.$row->selfassign->name .' (self) <span class="caret"></span></button>
                                                <a href="javascript:void(0)" data-id="' .$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="' .lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                            }
                                        }else{
                                            $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">' . lang('Assign') .' <span class="caret"></span></button>';
                                        }

                                    $assignedTo .= '<ul class="dropdown-menu" role="menu">
                                            <li class="dropdown-plus-title">' .lang('Assign') .' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                            <li>
                                                <a href="javascript:void(0);" id="selfassigid" data-id="' .$row->id .'">'.lang('Self Assign') .'</a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                                '.lang('Other Assign').'
                                                </a>
                                            </li>
                                        </ul>';
                                    $assignedTo .= '</div>';
                                }
                            }
                        }

                        return $assignedTo;
                    })
                    ->addColumn('action', function ($row) {
                        $action = '';
                        if(Auth::user()->can('Ticket Edit')){
                            $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) .'" class="btn btn-sm action-btns edit-testimonial"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Edit').'"></i></a>';
                        }else{
                            $action .= '~';
                        }
                        if(Auth::user()->can('Ticket Delete')){
                            $action .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-sm action-btns" id="show-delete" ><i class="feather feather-trash-2 text-danger" data-id="'.$row->id.'" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Delete') .'"></i></a>';
                        }else{
                            $action .= '~';
                        }

                        return $action;
                    })
                    ->rawColumns(['serial', 'id' ,'custname', 'mobilenumber', 'status', 'assignedTo','action'])// Ensure HTML is rendered as raw HTML
                    ->make(true);
    }

public function allticketsdata(Request $request)
{
            if (Auth::user()->dashboard == 'Admin') {
                $query = Ticket::select('tickets.*', 'groups_categories.group_id', 'groups_users.users_id', 'ticket_customfields.values')
                    ->leftJoin('groups_categories', 'groups_categories.category_id', 'tickets.category_id')
                    ->leftJoin('customers', 'customers.id', 'tickets.cust_id')
                    ->leftJoin('ticket_customfields', function ($join) {
                        $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                            ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
                    })
                    ->leftJoin('groups_users', 'groups_users.groups_id', 'groups_categories.group_id')
                    ->whereNull('tickets.emailticketfile')
                    ->latest('tickets.updated_at');
            } elseif (Auth::user()->dashboard === 'Employee' || Auth::user()->dashboard === null) {
                $groupexists = Groupsusers::where('users_id', Auth::id())->exists();

                if ($groupexists) {
                    $query = Ticket::select('tickets.*', 'groups_categories.group_id', 'groups_users.users_id', 'ticket_customfields.values')
                        ->leftJoin('groups_categories', 'groups_categories.category_id', 'tickets.category_id')
                        ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                        ->leftJoin('customers', 'customers.id', 'tickets.cust_id')
                        ->leftJoin('ticket_customfields', function ($join) {
                            $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                                ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
                        })
                        ->leftJoin('groups_users', 'groups_users.groups_id', 'groups_categories.group_id')
                        ->whereNotNull('groups_users.users_id')
                        ->where('ticketassignchildren.toassignUser_id', Auth::id())
                        ->where('groups_users.users_id', Auth::id())
                        ->whereNull('tickets.emailticketfile')
                        ->latest('tickets.updated_at');
                } else {
                    $query = Ticket::select('tickets.*', 'groups_categories.group_id', 'groups_users.users_id', 'ticket_customfields.values')
                        ->leftJoin('groups_categories', 'groups_categories.category_id', 'tickets.category_id')
                        ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                        ->leftJoin('customers', 'customers.id', 'tickets.cust_id')
                        ->leftJoin('ticket_customfields', function ($join) {
                            $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                                ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
                        })
                        ->leftJoin('groups_users', 'groups_users.groups_id', 'groups_categories.group_id')
                        ->whereNull('groups_users.users_id')
                        ->where('ticketassignchildren.toassignUser_id', Auth::id())
                        ->whereNull('tickets.emailticketfile')
                        ->latest('tickets.updated_at');
                }
            }

            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = '%' . $request->search['value'] . '%';
                $query->where(function ($q) use ($searchValue) {
                    $q->where('tickets.subject', 'like', $searchValue)
                        ->orWhere('ticket_customfields.values', 'like', $searchValue)
                        ->orWhere('tickets.ticket_id', 'like', $searchValue)
                        ->orWhereHas('cust', function ($qs) use ($searchValue) {
                            $qs->where('firstname', 'like', $searchValue)
                                ->orWhere('lastname', 'like', $searchValue);
                        });
                });
            }

            return DataTables::of($query)
                ->addIndexColumn() // ✅ Enables DT_RowIndex for serial number
                ->addColumn('serial', function ($row) {
                    return ''; // will be replaced by DT_RowIndex automatically
                })
                ->addColumn('id', function ($row) {
                    $html = '<a href="' . url('admin/ticket-view/' . $row->ticket_id) . '" class="fs-14 d-block font-weight-semibold">' . e($row->subject) . '</a>';
                    $html .= '<ul class="fs-13 font-weight-normal d-flex custom-ul">';
                    $html .= '<li class="pe-2 text-muted">#' . e($row->ticket_id) . '</li>';
                    $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" title="' . lang('Date') . '"><i class="fe fe-calendar me-1 fs-14"></i>' . $row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')) . '</li>';
                    $html .= '</ul>';
                    return $html;
                })
                ->addColumn('custname', function ($row) {
                    return e($row->cust->username) . ' (' . lang($row->cust->userType) . ')';
                })
                ->addColumn('mobilenumber', function ($row) {
                    return e($row->values);
                })
                ->addColumn('status', function ($row) {
                    $statusClass = match ($row->status) {
                        'New' => 'burnt-orange',
                        'Re-Open' => 'teal',
                        'Inprogress' => 'info',
                        'On-Hold' => 'warning',
                        default => 'danger',
                    };
                    return '<span class="badge badge-' . $statusClass . '">' . lang($row->status) . '</span>';
                })
            ->addColumn('assignedTo', function ($row) {
                $assignedTo = '';
                if(Auth::user()->can('Ticket Assign')){
                    if($row->status == 'Suspend' || $row->status == 'Closed'){
                        $assignedTo .= '<div class="btn-group">';
                            if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" disabled>'.lang('Multi Assign') .' <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="' .$row->id .'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>{{$row->selfassign->name}} (self) <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="'.$row->id.'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign').'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }else{
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>'.lang('Assign').'<span class="caret"></span></button>';
                            }
                        $assignedTo .= '</div>';
                    }else{
                        if($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id == null){
                            $assignedTo .= '<div class="btn-group">';
                            $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign').' <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">'.lang('Assign').' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="'.$row->id.'">'.lang('Self Assign').'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                        }else{
                             $assignedTo .= '<div class="btn-group">';
                                if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .'<span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Multi Assign') .'<span class="caret"></span></button>';
                                        $assignedTo .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .' <span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.$row->selfassign->name .' (self) <span class="caret"></span></button>
                                        <a href="javascript:void(0)" data-id="' .$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="' .lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }else{
                                    $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">' . lang('Assign') .' <span class="caret"></span></button>';
                                }

                               $assignedTo .= '<ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">' .lang('Assign') .' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="' .$row->id .'">'.lang('Self Assign') .'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>';
                            $assignedTo .= '</div>';
                        }
                    }
                }

                return $assignedTo;
            })

    //         ->addColumn('assignedTo', function ($row) {
    //                     $users = \App\Models\User::where('role', 'Agent')->get(); // Or whatever logic you use

    //                     if (Auth::user()->can('Ticket Assign') && !in_array($row->status, ['Suspend', 'Closed'])) {
    //                         $select = '<select class="form-select select2-assign" data-ticket-id="' . $row->id . '" style="width: 150px;">';
    //                         $select .= '<option value="">-- Select User --</option>';
    //                         foreach ($users as $user) {
    //                             $select .= '<option value="' . $user->id . '">' . e($user->name) . '</option>';
    //                         }
    //                         $select .= '</select>';
    //                         return $select;
    //                     }

    //                     return '<span class="badge bg-secondary">N/A</span>';
    // })


                    // Follow up
            ->addColumn('followup', function ($row) {
                            return '<button class="btn btn-sm btn-warning followup-btn"

                                        data-id="' . $row->id . '"

                                        data-username="' . e(Auth::user()->name) . '"
                                        data-useremail="' . e(Auth::user()->email) . '">
                                        <i class="fe fe-message-square me-1"></i> Follow Up
                                    </button>';
        })




                ->addColumn('action', function ($row) {
                    $action = '';
                    if (Auth::user()->can('Ticket Edit')) {
                        $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) . '" class="btn btn-sm action-btns"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" title="' . lang('Edit') . '"></i></a>';
                    }
                    if (Auth::user()->can('Ticket Delete')) {
                        $action .= '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-sm action-btns" id="show-delete"><i class="feather feather-trash-2 text-danger" data-bs-toggle="tooltip" title="' . lang('Delete') . '"></i></a>';
                    }
                    return $action;
                })
                ->rawColumns(['serial', 'id', 'custname', 'mobilenumber', 'status', 'assignedTo', 'followup', 'action'])

                ->make(true);
}


public function mailToTicket(Request $request)
{
    if ($request->ajax()) {
        if (Auth::user()->dashboard == 'Admin') {
            $query = Ticket::select('tickets.*', 'groups_categories.group_id', 'groups_users.users_id', 'ticket_customfields.values')
                ->leftJoin('groups_categories', 'groups_categories.category_id', 'tickets.category_id')
                ->leftJoin('customers', 'customers.id', 'tickets.cust_id')
                ->leftJoin('ticket_customfields', function ($join) {
                    $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                        ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
                })
                ->leftJoin('groups_users', 'groups_users.groups_id', 'groups_categories.group_id')
                ->whereNotNull('tickets.emailticketfile')
                ->latest('tickets.updated_at');
        } else {
            $groupexists = Groupsusers::where('users_id', Auth::id())->exists();

            if ($groupexists) {
                $query = Ticket::select('tickets.*', 'groups_categories.group_id', 'groups_users.users_id', 'ticket_customfields.values')
                    ->leftJoin('groups_categories', 'groups_categories.category_id', 'tickets.category_id')
                    ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                    ->leftJoin('customers', 'customers.id', 'tickets.cust_id')
                    ->leftJoin('ticket_customfields', function ($join) {
                        $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                            ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
                    })
                    ->leftJoin('groups_users', 'groups_users.groups_id', 'groups_categories.group_id')
                    ->whereNotNull('groups_users.users_id')
                    ->whereNotNull('tickets.emailticketfile')
                    ->where('ticketassignchildren.toassignUser_id', Auth::id())
                    ->where('groups_users.users_id', Auth::id())
                    ->latest('tickets.updated_at');
            } else {
                $query = Ticket::select('tickets.*', 'groups_categories.group_id', 'groups_users.users_id', 'ticket_customfields.values')
                    ->leftJoin('groups_categories', 'groups_categories.category_id', 'tickets.category_id')
                    ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                    ->leftJoin('customers', 'customers.id', 'tickets.cust_id')
                    ->leftJoin('ticket_customfields', function ($join) {
                        $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                            ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
                    })
                    ->leftJoin('groups_users', 'groups_users.groups_id', 'groups_categories.group_id')
                    ->whereNull('groups_users.users_id')
                    ->whereNotNull('tickets.emailticketfile')
                    ->where('ticketassignchildren.toassignUser_id', Auth::id())
                    ->latest('tickets.updated_at');
            }
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchValue = '%' . $request->search['value'] . '%';
            $query->where(function ($q) use ($searchValue) {
                $q->where('tickets.subject', 'like', $searchValue)
                    ->orWhere('ticket_customfields.values', 'like', $searchValue)
                    ->orWhere('tickets.ticket_id', 'like', $searchValue)
                    ->orWhere(function ($qs) use ($searchValue) {
                        $qs->where('customers.firstname', 'like', $searchValue)
                            ->orWhere('customers.lastname', 'like', $searchValue);
                    });
            });
        }

        return DataTables::of($query)
            ->addColumn('serial', function ($row) {
                return '';
            })

            // ✅ NEW: checkbox column
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
            })

            // ✅ ID Column: keep it for subject, etc.
            ->addColumn('id', function ($row) {
                $html = '<a href="' . url('admin/ticket-view/' . $row->id) . '" class="fs-14 d-block font-weight-semibold">' . e($row->subject) . '</a>';
                $html .= '<ul class="fs-13 font-weight-normal d-flex custom-ul">';
                $html .= '<li class="pe-2 text-muted">#' . e($row->ticket_id) . '</li>';
                $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" title="' . e(lang('Date')) . '">';
                $html .= '<i class="fe fe-calendar me-1 fs-14"></i>' . $row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')) . '</li>';
                $html .= '</ul>';
                return $html;
            })

            ->addColumn('custname', function ($row) {
                return $row->cust->username . ' (' . lang($row->cust->userType) . ')';
            })

            ->addColumn('mobilenumber', function ($row) {
                return $row->values;
            })

            ->addColumn('status', function ($row) {
                if ($row->status == "New")
                    return '<span class="badge badge-burnt-orange">' . lang($row->status) . '</span>';
                elseif ($row->status == "Re-Open")
                    return '<span class="badge badge-teal">' . lang($row->status) . '</span>';
                elseif ($row->status == "Inprogress")
                    return '<span class="badge badge-info">' . lang($row->status) . '</span>';
                elseif ($row->status == "On-Hold")
                    return '<span class="badge badge-warning">' . lang($row->status) . '</span>';
                else
                    return '<span class="badge badge-danger">' . lang($row->status) . '</span>';
            })

            ->addColumn('assignedTo', function ($row) {
                return '...'; // Copy your logic here if needed
            })

            ->addColumn('action', function ($row) {
                $action = '';
                if (Auth::user()->can('Ticket Edit')) {
                    $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) . '" class="btn btn-sm action-btns"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" title="' . lang('Edit') . '"></i></a>';
                }
                if (Auth::user()->can('Ticket Delete')) {
                    $action .= '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-sm action-btns" id="show-delete"><i class="feather feather-trash-2 text-danger" data-bs-toggle="tooltip" title="' . lang('Delete') . '"></i></a>';
                }
                return $action;
            })

            ->rawColumns(['serial', 'checkbox', 'id', 'custname', 'mobilenumber', 'status', 'assignedTo', 'action'])
            ->make(true);
    }

    // non-AJAX fallback
    $title = Apptitle::first();
    $footertext = Footertext::first();
    $seopage = Seosetting::first();
    $post = Pages::all();

    return view('admin.superadmindashboard.mailToTickets.index', compact('seopage', 'footertext', 'title', 'post'));
}



    public function recentticketsdata(Request $request)
    {

        if(Auth::user()->dashboard == 'Admin'){
            $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('customers','customers.id','tickets.cust_id')
            ->leftJoin('ticket_customfields', function ($join) {
                $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                     ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
            })
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.status', ['New'])
            ->whereNull('tickets.myassignuser_id')
            ->whereNull('tickets.selfassignuser_id')
            ->latest('tickets.updated_at');
        }else if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            $groupexists = Groupsusers::where('users_id', Auth::id())->exists();
            if($groupexists){
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                ->leftJoin('customers','customers.id','tickets.cust_id')
                ->leftJoin('ticket_customfields', function ($join) {
                    $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                        ->where('ticket_customfields.fieldnames', '=', "Mobile no.");
                })
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                // ->whereIn('tickets.status', ['New'])
                ->whereNotNull('groups_users.users_id')
                ->where('groups_users.users_id', Auth::id())
                ->where(function ($q) {
                    $q->where('ticketassignchildren.toassignUser_id', Auth::id())
                    ->orWhere('tickets.users_id', Auth::id())
                    ->orWhere('tickets.myassignuser_id', Auth::id());
                })
                ->latest('tickets.updated_at');
                // $query->where(function ($q) {
                //     $q->where('ticketassignchildren.toassignUser_id', Auth::id())
                //     ->orWhere('groups_users.users_id', Auth::id())
                //     ->orWhere('tickets.myassignuser_id', Auth::id());
                // });
            }
            else{
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                    ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                    ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                    ->leftJoin('customers','customers.id','tickets.cust_id')
                    ->leftJoin('ticket_customfields', function ($join) {
                        $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                            ->where('ticket_customfields.fieldnames', '=', "Mobile no.");
                    })
                    ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                    // ->whereIn('tickets.status', ['New'])
                    ->whereNull('groups_users.users_id')
                    // ->where('ticketassignchildren.toassignUser_id', Auth::id())
                    ->where(function ($q) {
                        $q->where('tickets.myassignuser_id', Auth::id())
                        ->orWhere('tickets.user_id', Auth::id())
                        ->orWhere('tickets.selfassignuser_id', Auth::id());
                    })
                    ->latest('tickets.updated_at');
                    // $query->where(function ($q) {
                    //     $q->where('ticketassignchildren.toassignUser_id', Auth::id())
                    //     ->orWhere('tickets.user_id', Auth::id())
                    //     ->orWhere('tickets.myassignuser_id', Auth::id());
                    // });
            }
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchValue = '%' . $request->search['value'] . '%';
            $query->where(function ($q) use ($searchValue) {
                $q->where('tickets.subject', 'like', $searchValue)
                ->orWhere('ticket_customfields.values', 'like', $searchValue)
                ->orWhere('tickets.ticket_id', 'like', $searchValue)
                ->orWhere (function ($qs) use ($searchValue) {
                    $qs->where('customers.firstname', 'like', $searchValue)
                        ->orWhere('customers.lastname', 'like', $searchValue);
                });
            });
        }

        $query->get();

        return DataTables::of($query)
            ->addColumn('serial', function ($row) {
                return '';
            })
            ->addColumn('id', function ($row) {
                $html = '<a href="ticket-view/' . $row->ticket_id .'" class="fs-14 d-block font-weight-semibold">' .$row->subject . '</a>
                <ul class="fs-13 font-weight-normal d-flex custom-ul">
                    <li class="pe-2 text-muted">#' . $row->ticket_id .'</span>
                    <li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Date').'"><i class="fe fe-calendar me-1 fs-14"></i> '.$row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')).'</li>';

                    if($row->priority != null)
                        if($row->priority == "Low")
                            $html .= '<li class="ps-5 pe-2 preference preference-low" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "High")
                            $html .= '<li class="ps-5 pe-2 preference preference-high" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "Critical")
                             $html .= '<li class="ps-5 pe-2 preference preference-critical" data-bs-toggle="tooltip" data-bs-placement="top" title="' .lang('Priority') . '"> '.lang($row->priority) . '</li>';

                        else
                            $html .= '<li class="ps-5 pe-2 preference preference-medium" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">' .lang($row->priority) .'</li>';
                    else
                        $html .= '~';

                    if($row->category_id != null)
                        if($row->category != null)
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.Str::limit($row->category->name, '40') .'</li>';
                        else
                            $html .= '~';
                    else
                        $html .= '~';

                    if($row->last_reply == null)
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'. lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' . $row->created_at->diffForHumans() .'</li>';
                    else
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' .$row->last_reply->diffForHumans() .'</li>';

                    if($row->purchasecodesupport != null)
                        if($row->purchasecodesupport == 'Supported')
                            $html .= '<li class="px-2 text-success font-weight-semibold">' . lang('Support Active') .'</li>';

                        if($row->purchasecodesupport == 'Expired')
                            $html .= '<li class="px-2 text-danger-dark font-weight-semibold">' .lang('Support Expired') .'</li>';

                $html .= '</ul>';
                return $html;
            })
            ->addColumn('custname', function ($row) {
                return $row->cust->username . ' (' . lang($row->cust->userType) . ')';
            })
            ->addColumn('mobilenumber', function ($row) {
                $mobileNo = $row->values;
                return $mobileNo;
            })
            ->addColumn('status', function ($row) {
                $status = '';
                if($row->status == "New")
                    $status = '<span class="badge badge-burnt-orange">' .lang($row->status) .'</span>';

                elseif($row->status == "Re-Open")
                    $status = '<span class="badge badge-teal">' . lang($row->status) .'</span>';

                elseif($row->status == "Inprogress")
                    $status = '<span class="badge badge-info">' . lang($row->status) .'</span>';

                elseif($row->status == "On-Hold")
                    $status = '<span class="badge badge-warning">' . lang($row->status) .'</span>';

                else
                    $status = '<span class="badge badge-danger">' . lang($row->status) .'</span>';

                return $status;
            })
            ->addColumn('assignedTo', function ($row) {
                $assignedTo = '';
                if(Auth::user()->can('Ticket Assign')){
                    if($row->status == 'Suspend' || $row->status == 'Closed'){
                        $assignedTo .= '<div class="btn-group">';
                            if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" disabled>'.lang('Multi Assign') .' <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="' .$row->id .'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>{{$row->selfassign->name}} (self) <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="'.$row->id.'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign').'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }else{
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>'.lang('Assign').'<span class="caret"></span></button>';
                            }
                        $assignedTo .= '</div>';
                    }else{
                        if($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id == null){
                            $assignedTo .= '<div class="btn-group">';
                            $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign').' <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">'.lang('Assign').' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="'.$row->id.'">'.lang('Self Assign').'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                        }else{
                             $assignedTo .= '<div class="btn-group">';
                                if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .'<span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Multi Assign') .'<span class="caret"></span></button>';
                                        $assignedTo .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .' <span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.$row->selfassign->name .' (self) <span class="caret"></span></button>
                                        <a href="javascript:void(0)" data-id="' .$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="' .lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }else{
                                    $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">' . lang('Assign') .' <span class="caret"></span></button>';
                                }

                               $assignedTo .= '<ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">' .lang('Assign') .' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="' .$row->id .'">'.lang('Self Assign') .'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>';
                            $assignedTo .= '</div>';
                        }
                    }
                }

                return $assignedTo;
            })
            ->addColumn('action', function ($row) {
                $action = '';
                if(Auth::user()->can('Ticket Edit')){
                    $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) .'" class="btn btn-sm action-btns edit-testimonial"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Edit').'"></i></a>';
                }else{
                    $action .= '~';
                }
                if(Auth::user()->can('Ticket Delete')){
                    $action .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-sm action-btns" id="show-delete" ><i class="feather feather-trash-2 text-danger" data-id="'.$row->id.'" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Delete') .'"></i></a>';
                }else{
                    $action .= '~';
                }

                return $action;
            })
            ->rawColumns(['serial', 'id' ,'custname', 'mobilenumber', 'status', 'assignedTo','action'])// Ensure HTML is rendered as raw HTML
            ->make(true);
    }

    public function activeticketsdata(Request $request)
    {

        if(Auth::user()->dashboard == 'Admin'){
            $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('customers','customers.id','tickets.cust_id')
            ->leftJoin('ticket_customfields', function ($join) {
                $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                     ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
            })
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.status', ['Re-Open','Inprogress','On-Hold'])
            ->latest('tickets.updated_at');
        }else if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            $groupexists = Groupsusers::where('users_id', Auth::id())->exists();
            if($groupexists){
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                ->leftJoin('customers','customers.id','tickets.cust_id')
                ->leftJoin('ticket_customfields', function ($join) {
                    $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                        ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
                })
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.status', ['Re-Open','Inprogress','On-Hold'])
                ->whereNotNull('groups_users.users_id')
                ->where('groups_users.users_id', Auth::id())
                ->latest('tickets.updated_at');
            }
            else{
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                    ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                    ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                    ->leftJoin('customers','customers.id','tickets.cust_id')
                    ->leftJoin('ticket_customfields', function ($join) {
                        $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                            ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
                    })
                    ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                    ->whereIn('tickets.status', ['Re-Open','Inprogress','On-Hold'])
                    ->whereNull('groups_users.users_id')
                    ->where('ticketassignchildren.toassignUser_id', Auth::id())
                    ->latest('tickets.updated_at');
            }
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchValue = '%' . $request->search['value'] . '%';
            $query->where(function ($q) use ($searchValue) {
                $q->where('tickets.subject', 'like', $searchValue)
                ->orWhere('ticket_customfields.values', 'like', $searchValue)
                ->orWhere('tickets.ticket_id', 'like', $searchValue)
                ->orWhere (function ($qs) use ($searchValue) {
                    $qs->where('customers.firstname', 'like', $searchValue)
                        ->orWhere('customers.lastname', 'like', $searchValue);
                });
            });
        }

        $query->get();

        return DataTables::of($query)
            ->addColumn('serial', function ($row) {
                return '';
            })
            ->addColumn('id', function ($row) {
                $html = '<a href="ticket-view/' . $row->ticket_id .'" class="fs-14 d-block font-weight-semibold">' .$row->subject . '</a>
                <ul class="fs-13 font-weight-normal d-flex custom-ul">
                    <li class="pe-2 text-muted">#' . $row->ticket_id .'</span>
                    <li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Date').'"><i class="fe fe-calendar me-1 fs-14"></i> '.$row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')).'</li>';

                    if($row->priority != null)
                        if($row->priority == "Low")
                            $html .= '<li class="ps-5 pe-2 preference preference-low" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "High")
                            $html .= '<li class="ps-5 pe-2 preference preference-high" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "Critical")
                             $html .= '<li class="ps-5 pe-2 preference preference-critical" data-bs-toggle="tooltip" data-bs-placement="top" title="' .lang('Priority') . '"> '.lang($row->priority) . '</li>';

                        else
                            $html .= '<li class="ps-5 pe-2 preference preference-medium" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">' .lang($row->priority) .'</li>';
                    else
                        $html .= '~';

                    if($row->category_id != null)
                        if($row->category != null)
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.Str::limit($row->category->name, '40') .'</li>';
                        else
                            $html .= '~';
                    else
                        $html .= '~';

                    if($row->last_reply == null)
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'. lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' . $row->created_at->diffForHumans() .'</li>';
                    else
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' .$row->last_reply->diffForHumans() .'</li>';

                    if($row->purchasecodesupport != null)
                        if($row->purchasecodesupport == 'Supported')
                            $html .= '<li class="px-2 text-success font-weight-semibold">' . lang('Support Active') .'</li>';

                        if($row->purchasecodesupport == 'Expired')
                            $html .= '<li class="px-2 text-danger-dark font-weight-semibold">' .lang('Support Expired') .'</li>';

                $html .= '</ul>';
                return $html;
            })
            ->addColumn('custname', function ($row) {
                return $row->cust->username . ' (' . lang($row->cust->userType) . ')';
            })
            ->addColumn('mobilenumber', function ($row) {
                $mobileNo = $row->values;
                return $mobileNo;
            })
            ->addColumn('status', function ($row) {
                $status = '';
                if($row->status == "New")
                    $status = '<span class="badge badge-burnt-orange">' .lang($row->status) .'</span>';

                elseif($row->status == "Re-Open")
                    $status = '<span class="badge badge-teal">' . lang($row->status) .'</span>';

                elseif($row->status == "Inprogress")
                    $status = '<span class="badge badge-info">' . lang($row->status) .'</span>';

                elseif($row->status == "On-Hold")
                    $status = '<span class="badge badge-warning">' . lang($row->status) .'</span>';

                else
                    $status = '<span class="badge badge-danger">' . lang($row->status) .'</span>';

                return $status;
            })
            ->addColumn('assignedTo', function ($row) {
                $assignedTo = '';
                if(Auth::user()->can('Ticket Assign')){
                    if($row->status == 'Suspend' || $row->status == 'Closed'){
                        $assignedTo .= '<div class="btn-group">';
                            if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" disabled>'.lang('Multi Assign') .' <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="' .$row->id .'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>{{$row->selfassign->name}} (self) <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="'.$row->id.'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign').'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }else{
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>'.lang('Assign').'<span class="caret"></span></button>';
                            }
                        $assignedTo .= '</div>';
                    }else{
                        if($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id == null){
                            $assignedTo .= '<div class="btn-group">';
                            $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign').' <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">'.lang('Assign').' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="'.$row->id.'">'.lang('Self Assign').'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                        }else{
                             $assignedTo .= '<div class="btn-group">';
                                if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .'<span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Multi Assign') .'<span class="caret"></span></button>';
                                        $assignedTo .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .' <span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.$row->selfassign->name .' (self) <span class="caret"></span></button>
                                        <a href="javascript:void(0)" data-id="' .$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="' .lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }else{
                                    $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">' . lang('Assign') .' <span class="caret"></span></button>';
                                }

                               $assignedTo .= '<ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">' .lang('Assign') .' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="' .$row->id .'">'.lang('Self Assign') .'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>';
                            $assignedTo .= '</div>';
                        }
                    }
                }

                return $assignedTo;
            })
            ->addColumn('action', function ($row) {
                $action = '';
                if(Auth::user()->can('Ticket Edit')){
                    $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) .'" class="btn btn-sm action-btns edit-testimonial"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Edit').'"></i></a>';
                }else{
                    $action .= '~';
                }
                if(Auth::user()->can('Ticket Delete')){
                    $action .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-sm action-btns" id="show-delete" ><i class="feather feather-trash-2 text-danger" data-id="'.$row->id.'" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Delete') .'"></i></a>';
                }else{
                    $action .= '~';
                }

                return $action;
            })
            ->rawColumns(['serial', 'id' ,'custname', 'mobilenumber', 'status', 'assignedTo','action'])// Ensure HTML is rendered as raw HTML
            ->make(true);
    }

    //superadmin dashborad
    public function adminDashboardtabledata()
    {
        $oneMonthBefore = date('Y-m-d h:i:s', strtotime('-11115 days'));
        $data['alltickets'] = Ticket::whereIn('status', ['New'])->where('created_at', '>', $oneMonthBefore)->latest('updated_at')->get();
        $data['ticketnote'] = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();



        return view('admin.superadmindashboard.dashboardtabledata')->with($data);
    }

    //Employee dashboard
    public function Dashboardtabledatas()
    {
        $groups =  Groups::where('groupstatus', '1')->get();

        $group_id = '';
        foreach($groups as $group){
            $group_id .= $group->id . ',';
        }


        $groupexists = Groupsusers::whereIn('groups_id', explode(',', substr($group_id,0,-1)))->where('users_id', Auth::id())->exists();


        // if there in group get group tickets
        if($groupexists){

            // All tickets
            $gticket = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.status', ['New'])
                ->whereNotNull('groups_users.users_id')
                ->where('groups_users.users_id', Auth::id())
                ->latest('tickets.updated_at')
                ->get();
            $data['gtickets'] = $gticket;
            // ticket note
            $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
            $data['ticketnote'] = $ticketnote;

        }
        // If no there in group we get the all tickets
        else{


            $gtickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.status', ['New'])
            ->whereNull('groups_users.users_id')
            ->latest('tickets.updated_at')
            ->get();;
            $data['gtickets'] = $gtickets;

            $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
            $data['ticketnote'] = $ticketnote;

        }


        return view('admin.dashboardtabledata')->with($data);
    }

    public function activeticket()
    {

        if(Auth::user()->dashboard == 'Admin'){
            return $this->adminallactiveticket();
        }
        if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            return $this->employeeallactiveticket();
        }



    }

    public function adminallactiveticket()
    {
        // $allactivetickets = Ticket::whereIn('status', ['Re-Open','Inprogress','On-Hold'])->latest('updated_at')->get();
        // $data['allactivetickets'] = $allactivetickets;

        $allactiveinprogresstickets = Ticket::where('status', 'Inprogress')->count();
        $data['allactiveinprogresstickets'] = $allactiveinprogresstickets;

        $allactivereopentickets = Ticket::whereIn('status', ['Re-Open'])->count();
        $data['allactivereopentickets'] = $allactivereopentickets;

        $allactiveonholdtickets = Ticket::whereIn('status', ['On-Hold'])->count();
        $data['allactiveonholdtickets'] = $allactiveonholdtickets;

        $allactiveassignedtickets = Ticket::whereIn('status', ['Re-Open','Inprogress','On-Hold'])->where(function($r){
            $r->whereNotNull('myassignuser_id')
            ->orWhereNotNull('selfassignuser_id');
        })->count();
        $data['allactiveassignedtickets'] = $allactiveassignedtickets;

        $allactiveoverduetickets = Ticket::whereIn('status', ['Re-Open','Inprogress','On-Hold'])->whereNotNull('overduestatus')->count();
        $data['allactiveoverduetickets'] = $allactiveoverduetickets;

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.superadmindashboard.activeticket' )->with($data);
    }

    public function employeeallactiveticket()
    {

        $groupexists = Groupsusers::where('users_id', Auth::id())->exists();

        // if there in group get group tickets
        if($groupexists){

            $activetickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.status', ['Re-Open','Inprogress','On-Hold'])
                ->whereNotNull('groups_users.users_id')
                ->where('groups_users.users_id', Auth::id())
                ->latest('tickets.updated_at')
                ->get();
            $data['gtickets'] = $activetickets;

            $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
            $data['ticketnote'] = $ticketnote;

        }
        // If no there in group we get the all tickets
        else{


            $activetickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.status', ['Re-Open','Inprogress','On-Hold'])
                ->whereNull('groups_users.users_id')
                ->latest('tickets.updated_at')
                ->get();
            $data['gtickets'] = $activetickets;
            $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
            $data['ticketnote'] = $ticketnote;

        }

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.userticket.viewticket.activeticket' )->with($data);

    }

    // Superadmin all closed Ticket
    public function closedticket()
    {

        // $allclosedtickets = Ticket::where('status', 'Closed')->latest('updated_at')->get();
        // $data['allclosedtickets'] = $allclosedtickets;

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.superadmindashboard.closedticket')->with($data);
    }

    public function closedticketsdata(Request $request)
    {

        if(Auth::user()->dashboard == 'Admin'){
            $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('customers','customers.id','tickets.cust_id')
            ->leftJoin('ticket_customfields', function ($join) {
                $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                     ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
            })
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.status', ['Closed'])
            ->latest('tickets.updated_at');
        }else if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            $groupexists = Groupsusers::where('users_id', Auth::id())->exists();
            if($groupexists){
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                ->leftJoin('customers','customers.id','tickets.cust_id')
                ->leftJoin('ticket_customfields', function ($join) {
                    $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                        ->where('ticket_customfields.fieldnames', '=', "Mobile no.");
                })
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.status', ['Closed'])
                ->whereNotNull('groups_users.users_id')
                ->where('groups_users.users_id', Auth::id())
                ->where(function ($q) {
                    $q->where('ticketassignchildren.toassignUser_id', Auth::id())
                    ->orWhere('tickets.users_id', Auth::id())
                    ->orWhere('tickets.myassignuser_id', Auth::id());
                })
                ->latest('tickets.updated_at');
            }
            else{
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                    ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                    ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                    ->leftJoin('customers','customers.id','tickets.cust_id')
                    ->leftJoin('ticket_customfields', function ($join) {
                        $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                            ->where('ticket_customfields.fieldnames', '=', "Mobile no.");
                    })
                    ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                    ->whereIn('tickets.status', ['Closed'])
                    ->whereNull('groups_users.users_id')
                    ->where(function ($q) {
                        $q->where('tickets.myassignuser_id', Auth::id())
                        ->orWhere('tickets.user_id', Auth::id())
                        ->orWhere('tickets.selfassignuser_id', Auth::id());
                    });
            }
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchValue = '%' . $request->search['value'] . '%';
            $query->where(function ($q) use ($searchValue) {
                $q->where('tickets.subject', 'like', $searchValue)
                ->orWhere('ticket_customfields.values', 'like', $searchValue)
                ->orWhere('tickets.ticket_id', 'like', $searchValue)
                ->orWhere (function ($qs) use ($searchValue) {
                    $qs->where('customers.firstname', 'like', $searchValue)
                        ->orWhere('customers.lastname', 'like', $searchValue);
                });
            });
        }

        $query->get();

        return DataTables::of($query)
            ->addColumn('serial', function ($row) {
                return '';
            })
            ->addColumn('id', function ($row) {
                $html = '<a href="ticket-view/' . $row->ticket_id .'" class="fs-14 d-block font-weight-semibold">' .$row->subject . '</a>
                <ul class="fs-13 font-weight-normal d-flex custom-ul">
                    <li class="pe-2 text-muted">#' . $row->ticket_id .'</span>
                    <li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Date').'"><i class="fe fe-calendar me-1 fs-14"></i> '.$row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')).'</li>';

                    if($row->priority != null)
                        if($row->priority == "Low")
                            $html .= '<li class="ps-5 pe-2 preference preference-low" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "High")
                            $html .= '<li class="ps-5 pe-2 preference preference-high" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "Critical")
                             $html .= '<li class="ps-5 pe-2 preference preference-critical" data-bs-toggle="tooltip" data-bs-placement="top" title="' .lang('Priority') . '"> '.lang($row->priority) . '</li>';

                        else
                            $html .= '<li class="ps-5 pe-2 preference preference-medium" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">' .lang($row->priority) .'</li>';
                    else
                        $html .= '~';

                    if($row->category_id != null)
                        if($row->category != null)
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.Str::limit($row->category->name, '40') .'</li>';
                        else
                            $html .= '~';
                    else
                        $html .= '~';

                    if($row->last_reply == null)
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'. lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' . $row->created_at->diffForHumans() .'</li>';
                    else
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' .$row->last_reply->diffForHumans() .'</li>';

                    if($row->purchasecodesupport != null)
                        if($row->purchasecodesupport == 'Supported')
                            $html .= '<li class="px-2 text-success font-weight-semibold">' . lang('Support Active') .'</li>';

                        if($row->purchasecodesupport == 'Expired')
                            $html .= '<li class="px-2 text-danger-dark font-weight-semibold">' .lang('Support Expired') .'</li>';

                $html .= '</ul>';
                return $html;
            })
            ->addColumn('custname', function ($row) {
                return $row->cust->username . ' (' . lang($row->cust->userType) . ')';
            })
            ->addColumn('mobilenumber', function ($row) {
                $mobileNo = $row->values;
                return $mobileNo;
            })
            ->addColumn('status', function ($row) {
                $status = '';
                if($row->status == "New")
                    $status = '<span class="badge badge-burnt-orange">' .lang($row->status) .'</span>';

                elseif($row->status == "Re-Open")
                    $status = '<span class="badge badge-teal">' . lang($row->status) .'</span>';

                elseif($row->status == "Inprogress")
                    $status = '<span class="badge badge-info">' . lang($row->status) .'</span>';

                elseif($row->status == "On-Hold")
                    $status = '<span class="badge badge-warning">' . lang($row->status) .'</span>';

                else
                    $status = '<span class="badge badge-danger">' . lang($row->status) .'</span>';

                return $status;
            })
            ->addColumn('assignedTo', function ($row) {
                $assignedTo = '';
                if(Auth::user()->can('Ticket Assign')){
                    if($row->status == 'Suspend' || $row->status == 'Closed'){
                        $assignedTo .= '<div class="btn-group">';
                            if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" disabled>'.lang('Multi Assign') .' <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="' .$row->id .'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>{{$row->selfassign->name}} (self) <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="'.$row->id.'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign').'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }else{
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>'.lang('Assign').'<span class="caret"></span></button>';
                            }
                        $assignedTo .= '</div>';
                    }else{
                        if($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id == null){
                            $assignedTo .= '<div class="btn-group">';
                            $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign').' <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">'.lang('Assign').' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="'.$row->id.'">'.lang('Self Assign').'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                        }else{
                             $assignedTo .= '<div class="btn-group">';
                                if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .'<span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Multi Assign') .'<span class="caret"></span></button>';
                                        $assignedTo .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .' <span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.$row->selfassign->name .' (self) <span class="caret"></span></button>
                                        <a href="javascript:void(0)" data-id="' .$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="' .lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }else{
                                    $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">' . lang('Assign') .' <span class="caret"></span></button>';
                                }

                               $assignedTo .= '<ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">' .lang('Assign') .' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="' .$row->id .'">'.lang('Self Assign') .'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>';
                            $assignedTo .= '</div>';
                        }
                    }
                }

                return $assignedTo;
            })
            ->addColumn('action', function ($row) {
                $action = '';
                if(Auth::user()->can('Ticket Edit')){
                    $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) .'" class="btn btn-sm action-btns edit-testimonial"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Edit').'"></i></a>';
                }else{
                    $action .= '~';
                }
                if(Auth::user()->can('Ticket Delete')){
                    $action .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-sm action-btns" id="show-delete" ><i class="feather feather-trash-2 text-danger" data-id="'.$row->id.'" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Delete') .'"></i></a>';
                }else{
                    $action .= '~';
                }

                return $action;
            })
            ->rawColumns(['serial', 'id' ,'custname', 'mobilenumber', 'status', 'assignedTo','action'])// Ensure HTML is rendered as raw HTML
            ->make(true);
    }

    public function assignedTickets()
    {

        $assignedtickets = Ticket::where('myassignuser_id', Auth::id())->whereNull('selfassignuser_id')->where('status', '!=' ,'Closed')->latest('updated_at')->get();
        $data['gtickets'] = $assignedtickets;

        $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
        $data['ticketnote'] = $ticketnote;

        $active = Ticket::whereIn('status', ['New', 'Re-Open','Inprogress'])->get();

        $closed = Ticket::where('status', 'Closed')->get();

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $agent = User::count();
        $data['agent'] = $agent;

        $customer = User::count();
        $data['customer'] = $customer;


        $assignedticketsnew = Ticket::where('myassignuser_id', Auth::id())->whereNull('selfassignuser_id')->where('status', 'New')->count();
        $data['assignedticketsnew'] = $assignedticketsnew;

        $assignedticketsinprogress = Ticket::where('myassignuser_id', Auth::id())->whereNull('selfassignuser_id')->where('status', 'Inprogress')->count();
        $data['assignedticketsinprogress'] = $assignedticketsinprogress;

        $assignedticketsonhold = Ticket::where('myassignuser_id', Auth::id())->whereNull('selfassignuser_id')->where('status', 'On-Hold')->count();
        $data['assignedticketsonhold'] = $assignedticketsonhold;

        $assignedticketsreopen = Ticket::where('myassignuser_id', Auth::id())->whereNull('selfassignuser_id')->where('status', 'Re-Open')->count();
        $data['assignedticketsreopen'] = $assignedticketsreopen;

        $assignedticketsoverdue = Ticket::where('myassignuser_id', Auth::id())->whereNull('selfassignuser_id')->where('overduestatus', 'Overdue')->count();
        $data['assignedticketsoverdue'] = $assignedticketsoverdue;

        $assignedticketsclosed = Ticket::where('myassignuser_id', Auth::id())->whereNull('selfassignuser_id')->where('status', 'Closed')->count();
        $data['assignedticketsclosed'] = $assignedticketsclosed;

        return view('admin.assignedtickets.index', compact('active','closed'))->with($data);
    }

    public function myassignedTickets()
    {

        // $myassignedtickets = Ticket::select('tickets.*', 'ticketassignchildren.toassignuser_id')->whereNull('selfassignuser_id')->leftjoin('ticketassignchildren', 'ticketassignchildren.ticket_id', 'tickets.id')->where('status', '!=' ,'Closed')->where('status', '!=' ,'Suspend')->latest('updated_at')->get();
        // $data['gtickets'] = $myassignedtickets;

        $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
        $data['ticketnote'] = $ticketnote;


        $active = Ticket::whereIn('status', ['New', 'Re-Open','Inprogress'])->get();

        $closed = Ticket::where('status', 'Closed')->get();

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $agent = User::count();
        $data['agent'] = $agent;

        $customer = User::count();
        $data['customer'] = $customer;

        return view('admin.assignedtickets.myassignedticket', compact('active','closed'))->with($data);
    }

    public function allmyassignedTickets(Request $request)
    {
        $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values", "ticketassignchildren.toassignuser_id")
        ->leftjoin('ticketassignchildren', 'ticketassignchildren.ticket_id', 'tickets.id')
        ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
        ->leftJoin('customers','customers.id','tickets.cust_id')
        ->leftJoin('ticket_customfields', function ($join) {
            $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                    ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
        })
        ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
        ->whereNull('tickets.selfassignuser_id')
        ->where('tickets.status', '!=' ,'Closed')
        ->where('tickets.status', '!=' ,'Suspend')
        ->where('ticketassignchildren.toassignuser_id', Auth::id())
        ->latest('tickets.updated_at');

        if ($request->has('search') && !empty($request->search)) {
            $searchValue = '%' . $request->search['value'] . '%';
            $query->where(function ($q) use ($searchValue) {
                $q->where('tickets.subject', 'like', $searchValue)
                ->orWhere('ticket_customfields.values', 'like', $searchValue)
                ->orWhere('tickets.ticket_id', 'like', $searchValue)
                ->orWhere (function ($qs) use ($searchValue) {
                    $qs->where('customers.firstname', 'like', $searchValue)
                        ->orWhere('customers.lastname', 'like', $searchValue);
                });
            });
        }

        $query->get();

        return DataTables::of($query)
            ->addColumn('serial', function ($row) {
                return '';
            })
            ->addColumn('id', function ($row) {
                $html = '<a href="ticket-view/' . $row->ticket_id .'" class="fs-14 d-block font-weight-semibold">' .$row->subject . '</a>
                <ul class="fs-13 font-weight-normal d-flex custom-ul">
                    <li class="pe-2 text-muted">#' . $row->ticket_id .'</span>
                    <li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Date').'"><i class="fe fe-calendar me-1 fs-14"></i> '.$row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')).'</li>';

                    if($row->priority != null)
                        if($row->priority == "Low")
                            $html .= '<li class="ps-5 pe-2 preference preference-low" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "High")
                            $html .= '<li class="ps-5 pe-2 preference preference-high" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "Critical")
                             $html .= '<li class="ps-5 pe-2 preference preference-critical" data-bs-toggle="tooltip" data-bs-placement="top" title="' .lang('Priority') . '"> '.lang($row->priority) . '</li>';

                        else
                            $html .= '<li class="ps-5 pe-2 preference preference-medium" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">' .lang($row->priority) .'</li>';
                    else
                        $html .= '~';

                    if($row->category_id != null)
                        if($row->category != null)
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.Str::limit($row->category->name, '40') .'</li>';
                        else
                            $html .= '~';
                    else
                        $html .= '~';

                    if($row->last_reply == null)
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'. lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' . $row->created_at->diffForHumans() .'</li>';
                    else
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' .$row->last_reply->diffForHumans() .'</li>';

                    if($row->purchasecodesupport != null)
                        if($row->purchasecodesupport == 'Supported')
                            $html .= '<li class="px-2 text-success font-weight-semibold">' . lang('Support Active') .'</li>';

                        if($row->purchasecodesupport == 'Expired')
                            $html .= '<li class="px-2 text-danger-dark font-weight-semibold">' .lang('Support Expired') .'</li>';

                $html .= '</ul>';
                return $html;
            })
            ->addColumn('custname', function ($row) {
                return $row->cust->username . ' (' . lang($row->cust->userType) . ')';
            })
            ->addColumn('mobilenumber', function ($row) {
                $mobileNo = $row->values;
                return $mobileNo;
            })
            ->addColumn('status', function ($row) {
                $status = '';
                if($row->status == "New")
                    $status = '<span class="badge badge-burnt-orange">' .lang($row->status) .'</span>';

                elseif($row->status == "Re-Open")
                    $status = '<span class="badge badge-teal">' . lang($row->status) .'</span>';

                elseif($row->status == "Inprogress")
                    $status = '<span class="badge badge-info">' . lang($row->status) .'</span>';

                elseif($row->status == "On-Hold")
                    $status = '<span class="badge badge-warning">' . lang($row->status) .'</span>';

                else
                    $status = '<span class="badge badge-danger">' . lang($row->status) .'</span>';

                return $status;
            })
            ->addColumn('assignedTo', function ($row) {
                $assignedTo = '';
                if(Auth::user()->can('Ticket Assign')){
                    if($row->status == 'Suspend' || $row->status == 'Closed'){
                        $assignedTo .= '<div class="btn-group">';
                            if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" disabled>'.lang('Multi Assign') .' <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="' .$row->id .'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>{{$row->selfassign->name}} (self) <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="'.$row->id.'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign').'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }else{
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>'.lang('Assign').'<span class="caret"></span></button>';
                            }
                        $assignedTo .= '</div>';
                    }else{
                        if($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id == null){
                            $assignedTo .= '<div class="btn-group">';
                            $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign').' <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">'.lang('Assign').' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="'.$row->id.'">'.lang('Self Assign').'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                        }else{
                             $assignedTo .= '<div class="btn-group">';
                                if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .'<span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Multi Assign') .'<span class="caret"></span></button>';
                                        $assignedTo .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .' <span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.$row->selfassign->name .' (self) <span class="caret"></span></button>
                                        <a href="javascript:void(0)" data-id="' .$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="' .lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }else{
                                    $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">' . lang('Assign') .' <span class="caret"></span></button>';
                                }

                               $assignedTo .= '<ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">' .lang('Assign') .' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="' .$row->id .'">'.lang('Self Assign') .'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>';
                            $assignedTo .= '</div>';
                        }
                    }
                }

                return $assignedTo;
            })
            ->addColumn('action', function ($row) {
                $action = '';
                if(Auth::user()->can('Ticket Edit')){
                    $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) .'" class="btn btn-sm action-btns edit-testimonial"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Edit').'"></i></a>';
                }else{
                    $action .= '~';
                }
                if(Auth::user()->can('Ticket Delete')){
                    $action .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-sm action-btns" id="show-delete" ><i class="feather feather-trash-2 text-danger" data-id="'.$row->id.'" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Delete') .'"></i></a>';
                }else{
                    $action .= '~';
                }

                return $action;
            })
            ->rawColumns(['serial', 'id' ,'custname', 'mobilenumber', 'status', 'assignedTo','action'])// Ensure HTML is rendered as raw HTML
            ->make(true);
    }

    public function onholdticket()
    {


        if(Auth::user()->dashboard == 'Admin'){
            return $this->adminonholdticket();
        }
        if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            return $this->employeeonholdticket();
        }



    }

    public function adminonholdticket()
    {
        // $allonholdtickets = Ticket::where('status','On-Hold')->latest('updated_at')->get();
        // $data['allonholdtickets'] = $allonholdtickets;

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.superadmindashboard.onholdtickets')->with($data);
    }

    public function employeeonholdticket()
    {


        $groupexists = Groupsusers::where('users_id', Auth::id())->exists();

        // if there in group get group tickets
        // if($groupexists){

        //     $onholdtickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
        //         ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
        //         ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
        //         ->where('status','On-Hold')
        //         ->whereNotNull('groups_users.users_id')
        //         ->where('groups_users.users_id', Auth::id())
        //         ->get();
        //     $data['gtickets'] = $onholdtickets;

        //     $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
        //     $data['ticketnote'] = $ticketnote;

        // }
        // // If no there in group we get the all tickets
        // else{

        //     $onholdtickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
        //     ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
        //     ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
        //     ->where('status','On-Hold')
        //     ->whereNull('groups_users.users_id')
        //     ->get();
        //     $data['gtickets'] = $onholdtickets;

        //     $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
        //     $data['ticketnote'] = $ticketnote;

        // }

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.assignedtickets.onholdtickets')->with($data);
    }

    public function holdticketsdata(Request $request)
    {
        if(Auth::user()->dashboard == 'Admin'){
            $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('customers','customers.id','tickets.cust_id')
            ->leftJoin('ticket_customfields', function ($join) {
                $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                     ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
            })
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.status', ['On-Hold'])
            ->latest('tickets.updated_at');
        }else if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            $groupexists = Groupsusers::where('users_id', Auth::id())->exists();
            if($groupexists){
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                ->leftJoin('customers','customers.id','tickets.cust_id')
                ->leftJoin('ticket_customfields', function ($join) {
                    $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                        ->where('ticket_customfields.fieldnames', '=', "Mobile no.");
                })
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.status', ['On-Hold'])
                ->whereNotNull('groups_users.users_id')
                ->where('groups_users.users_id', Auth::id())
                ->where(function ($q) {
                    $q->where('ticketassignchildren.toassignUser_id', Auth::id())
                    ->orWhere('tickets.users_id', Auth::id())
                    ->orWhere('tickets.myassignuser_id', Auth::id());
                })
                ->latest('tickets.updated_at');
            }
            else{
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                    ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                    ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                    ->leftJoin('customers','customers.id','tickets.cust_id')
                    ->leftJoin('ticket_customfields', function ($join) {
                        $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                            ->where('ticket_customfields.fieldnames', '=', "Mobile no.");
                    })
                    ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                    ->whereIn('tickets.status', ['On-Hold'])
                    ->whereNull('groups_users.users_id')
                    ->where(function ($q) {
                        $q->where('tickets.myassignuser_id', Auth::id())
                        ->orWhere('tickets.user_id', Auth::id())
                        ->orWhere('tickets.selfassignuser_id', Auth::id());
                    });
            }
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchValue = '%' . $request->search['value'] . '%';
            $query->where(function ($q) use ($searchValue) {
                $q->where('tickets.subject', 'like', $searchValue)
                ->orWhere('ticket_customfields.values', 'like', $searchValue)
                ->orWhere('tickets.ticket_id', 'like', $searchValue)
                ->orWhere (function ($qs) use ($searchValue) {
                    $qs->where('customers.firstname', 'like', $searchValue)
                        ->orWhere('customers.lastname', 'like', $searchValue);
                });
            });
        }

        $query->get();

        return DataTables::of($query)
            ->addColumn('serial', function ($row) {
                return '';
            })
            ->addColumn('id', function ($row) {
                $html = '<a href="ticket-view/' . $row->ticket_id .'" class="fs-14 d-block font-weight-semibold">' .$row->subject . '</a>
                <ul class="fs-13 font-weight-normal d-flex custom-ul">
                    <li class="pe-2 text-muted">#' . $row->ticket_id .'</span>
                    <li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Date').'"><i class="fe fe-calendar me-1 fs-14"></i> '.$row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')).'</li>';

                    if($row->priority != null)
                        if($row->priority == "Low")
                            $html .= '<li class="ps-5 pe-2 preference preference-low" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "High")
                            $html .= '<li class="ps-5 pe-2 preference preference-high" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "Critical")
                             $html .= '<li class="ps-5 pe-2 preference preference-critical" data-bs-toggle="tooltip" data-bs-placement="top" title="' .lang('Priority') . '"> '.lang($row->priority) . '</li>';

                        else
                            $html .= '<li class="ps-5 pe-2 preference preference-medium" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">' .lang($row->priority) .'</li>';
                    else
                        $html .= '~';

                    if($row->category_id != null)
                        if($row->category != null)
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.Str::limit($row->category->name, '40') .'</li>';
                        else
                            $html .= '~';
                    else
                        $html .= '~';

                    if($row->last_reply == null)
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'. lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' . $row->created_at->diffForHumans() .'</li>';
                    else
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' .$row->last_reply->diffForHumans() .'</li>';

                    if($row->purchasecodesupport != null)
                        if($row->purchasecodesupport == 'Supported')
                            $html .= '<li class="px-2 text-success font-weight-semibold">' . lang('Support Active') .'</li>';

                        if($row->purchasecodesupport == 'Expired')
                            $html .= '<li class="px-2 text-danger-dark font-weight-semibold">' .lang('Support Expired') .'</li>';

                $html .= '</ul>';
                return $html;
            })
            ->addColumn('custname', function ($row) {
                return $row->cust->username . ' (' . lang($row->cust->userType) . ')';
            })
            ->addColumn('mobilenumber', function ($row) {
                $mobileNo = $row->values;
                return $mobileNo;
            })
            ->addColumn('status', function ($row) {
                $status = '';
                if($row->status == "New")
                    $status = '<span class="badge badge-burnt-orange">' .lang($row->status) .'</span>';

                elseif($row->status == "Re-Open")
                    $status = '<span class="badge badge-teal">' . lang($row->status) .'</span>';

                elseif($row->status == "Inprogress")
                    $status = '<span class="badge badge-info">' . lang($row->status) .'</span>';

                elseif($row->status == "On-Hold")
                    $status = '<span class="badge badge-warning">' . lang($row->status) .'</span>';

                else
                    $status = '<span class="badge badge-danger">' . lang($row->status) .'</span>';

                return $status;
            })
            ->addColumn('assignedTo', function ($row) {
                $assignedTo = '';
                if(Auth::user()->can('Ticket Assign')){
                    if($row->status == 'Suspend' || $row->status == 'Closed'){
                        $assignedTo .= '<div class="btn-group">';
                            if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" disabled>'.lang('Multi Assign') .' <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="' .$row->id .'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>{{$row->selfassign->name}} (self) <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="'.$row->id.'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign').'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }else{
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>'.lang('Assign').'<span class="caret"></span></button>';
                            }
                        $assignedTo .= '</div>';
                    }else{
                        if($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id == null){
                            $assignedTo .= '<div class="btn-group">';
                            $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign').' <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">'.lang('Assign').' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="'.$row->id.'">'.lang('Self Assign').'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                        }else{
                             $assignedTo .= '<div class="btn-group">';
                                if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .'<span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Multi Assign') .'<span class="caret"></span></button>';
                                        $assignedTo .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .' <span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.$row->selfassign->name .' (self) <span class="caret"></span></button>
                                        <a href="javascript:void(0)" data-id="' .$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="' .lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }else{
                                    $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">' . lang('Assign') .' <span class="caret"></span></button>';
                                }

                               $assignedTo .= '<ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">' .lang('Assign') .' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="' .$row->id .'">'.lang('Self Assign') .'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>';
                            $assignedTo .= '</div>';
                        }
                    }
                }

                return $assignedTo;
            })
            ->addColumn('action', function ($row) {
                $action = '';
                if(Auth::user()->can('Ticket Edit')){
                    $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) .'" class="btn btn-sm action-btns edit-testimonial"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Edit').'"></i></a>';
                }else{
                    $action .= '~';
                }
                if(Auth::user()->can('Ticket Delete')){
                    $action .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-sm action-btns" id="show-delete" ><i class="feather feather-trash-2 text-danger" data-id="'.$row->id.'" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Delete') .'"></i></a>';
                }else{
                    $action .= '~';
                }

                return $action;
            })
            ->rawColumns(['serial', 'id' ,'custname', 'mobilenumber', 'status', 'assignedTo','action'])// Ensure HTML is rendered as raw HTML
            ->make(true);
    }

    public function overdueticket()
    {

        if(Auth::user()->dashboard == 'Admin'){
            return $this->adminoverdueticket();
        }
        if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            return $this->employeeoverdueticket();
        }


    }


    public function adminoverdueticket()
    {
        // $alloverduetickets = Ticket::whereIn('overduestatus', ['Overdue'])->latest('updated_at')->get();
        // $data['alloverduetickets'] = $alloverduetickets;

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.superadmindashboard.overdueticket')->with($data);

    }


    public function employeeoverdueticket()
    {

        $groupexists = Groupsusers::where('users_id', Auth::id())->exists();

        // if there in group get group tickets
        // if($groupexists){
        //     $overduetickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
        //         ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
        //         ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
        //         ->whereIn('overduestatus', ['Overdue'])
        //         ->whereNotNull('groups_users.users_id')
        //         ->where('groups_users.users_id', Auth::id())
        //         ->get();
        //     $data['gtickets'] = $overduetickets;

        //     $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
        //     $data['ticketnote'] = $ticketnote;

        // }
        // // If no there in group we get the all tickets
        // else{
        //     $overduetickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
        //     ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
        //     ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
        //     ->whereIn('overduestatus', ['Overdue'])
        //     ->whereNull('groups_users.users_id')
        //     ->get();
        //     $data['gtickets'] = $overduetickets;

        //     $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
        //     $data['ticketnote'] = $ticketnote;

        // }

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $tickets = Ticket::whereIn('overduestatus', ['Overdue'])->get();

        return view('admin.assignedtickets.overdueticket', compact('tickets'))->with($data);
    }

    public function overdueticketsdata(Request $request)
    {
        if(Auth::user()->dashboard == 'Admin'){
            $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('customers','customers.id','tickets.cust_id')
            ->leftJoin('ticket_customfields', function ($join) {
                $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                     ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
            })
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.overduestatus', ['Overdue'])
            ->latest('tickets.updated_at');
        }else if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            $groupexists = Groupsusers::where('users_id', Auth::id())->exists();
            if($groupexists){
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                ->leftJoin('customers','customers.id','tickets.cust_id')
                ->leftJoin('ticket_customfields', function ($join) {
                    $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                        ->where('ticket_customfields.fieldnames', '=', "Mobile no.");
                })
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.overduestatus', ['Overdue'])
                ->whereNotNull('groups_users.users_id')
                ->where('groups_users.users_id', Auth::id())
                ->where(function ($q) {
                    $q->where('ticketassignchildren.toassignUser_id', Auth::id())
                    ->orWhere('tickets.users_id', Auth::id())
                    ->orWhere('tickets.myassignuser_id', Auth::id());
                })
                ->latest('tickets.updated_at');
            }
            else{
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                    ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                    ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                    ->leftJoin('customers','customers.id','tickets.cust_id')
                    ->leftJoin('ticket_customfields', function ($join) {
                        $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                            ->where('ticket_customfields.fieldnames', '=', "Mobile no.");
                    })
                    ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                    ->whereIn('tickets.overduestatus', ['Overdue'])
                    ->whereNull('groups_users.users_id')
                    ->where(function ($q) {
                        $q->where('tickets.myassignuser_id', Auth::id())
                        ->orWhere('tickets.user_id', Auth::id())
                        ->orWhere('tickets.selfassignuser_id', Auth::id());
                    });
            }
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchValue = '%' . $request->search['value'] . '%';
            $query->where(function ($q) use ($searchValue) {
                $q->where('tickets.subject', 'like', $searchValue)
                ->orWhere('ticket_customfields.values', 'like', $searchValue)
                ->orWhere('tickets.ticket_id', 'like', $searchValue)
                ->orWhere (function ($qs) use ($searchValue) {
                    $qs->where('customers.firstname', 'like', $searchValue)
                        ->orWhere('customers.lastname', 'like', $searchValue);
                });
            });
        }

        $query->get();

        return DataTables::of($query)
            ->addColumn('serial', function ($row) {
                return '';
            })
            ->addColumn('id', function ($row) {
                $html = '<a href="ticket-view/' . $row->ticket_id .'" class="fs-14 d-block font-weight-semibold">' .$row->subject . '</a>
                <ul class="fs-13 font-weight-normal d-flex custom-ul">
                    <li class="pe-2 text-muted">#' . $row->ticket_id .'</span>
                    <li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Date').'"><i class="fe fe-calendar me-1 fs-14"></i> '.$row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')).'</li>';

                    if($row->priority != null)
                        if($row->priority == "Low")
                            $html .= '<li class="ps-5 pe-2 preference preference-low" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "High")
                            $html .= '<li class="ps-5 pe-2 preference preference-high" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "Critical")
                             $html .= '<li class="ps-5 pe-2 preference preference-critical" data-bs-toggle="tooltip" data-bs-placement="top" title="' .lang('Priority') . '"> '.lang($row->priority) . '</li>';

                        else
                            $html .= '<li class="ps-5 pe-2 preference preference-medium" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">' .lang($row->priority) .'</li>';
                    else
                        $html .= '~';

                    if($row->category_id != null)
                        if($row->category != null)
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.Str::limit($row->category->name, '40') .'</li>';
                        else
                            $html .= '~';
                    else
                        $html .= '~';

                    if($row->last_reply == null)
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'. lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' . $row->created_at->diffForHumans() .'</li>';
                    else
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' .$row->last_reply->diffForHumans() .'</li>';

                    if($row->purchasecodesupport != null)
                        if($row->purchasecodesupport == 'Supported')
                            $html .= '<li class="px-2 text-success font-weight-semibold">' . lang('Support Active') .'</li>';

                        if($row->purchasecodesupport == 'Expired')
                            $html .= '<li class="px-2 text-danger-dark font-weight-semibold">' .lang('Support Expired') .'</li>';

                $html .= '</ul>';
                return $html;
            })
            ->addColumn('custname', function ($row) {
                return $row->cust->username . ' (' . lang($row->cust->userType) . ')';
            })
            ->addColumn('mobilenumber', function ($row) {
                $mobileNo = $row->values;
                return $mobileNo;
            })
            ->addColumn('status', function ($row) {
                $status = '';
                if($row->status == "New")
                    $status = '<span class="badge badge-burnt-orange">' .lang($row->status) .'</span>';

                elseif($row->status == "Re-Open")
                    $status = '<span class="badge badge-teal">' . lang($row->status) .'</span>';

                elseif($row->status == "Inprogress")
                    $status = '<span class="badge badge-info">' . lang($row->status) .'</span>';

                elseif($row->status == "On-Hold")
                    $status = '<span class="badge badge-warning">' . lang($row->status) .'</span>';

                else
                    $status = '<span class="badge badge-danger">' . lang($row->status) .'</span>';

                return $status;
            })
            ->addColumn('assignedTo', function ($row) {
                $assignedTo = '';
                if(Auth::user()->can('Ticket Assign')){
                    if($row->status == 'Suspend' || $row->status == 'Closed'){
                        $assignedTo .= '<div class="btn-group">';
                            if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" disabled>'.lang('Multi Assign') .' <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="' .$row->id .'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>{{$row->selfassign->name}} (self) <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="'.$row->id.'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign').'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }else{
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>'.lang('Assign').'<span class="caret"></span></button>';
                            }
                        $assignedTo .= '</div>';
                    }else{
                        if($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id == null){
                            $assignedTo .= '<div class="btn-group">';
                            $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign').' <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">'.lang('Assign').' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="'.$row->id.'">'.lang('Self Assign').'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                        }else{
                             $assignedTo .= '<div class="btn-group">';
                                if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .'<span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Multi Assign') .'<span class="caret"></span></button>';
                                        $assignedTo .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .' <span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.$row->selfassign->name .' (self) <span class="caret"></span></button>
                                        <a href="javascript:void(0)" data-id="' .$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="' .lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }else{
                                    $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">' . lang('Assign') .' <span class="caret"></span></button>';
                                }

                               $assignedTo .= '<ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">' .lang('Assign') .' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="' .$row->id .'">'.lang('Self Assign') .'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>';
                            $assignedTo .= '</div>';
                        }
                    }
                }

                return $assignedTo;
            })
            ->addColumn('action', function ($row) {
                $action = '';
                if(Auth::user()->can('Ticket Edit')){
                    $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) .'" class="btn btn-sm action-btns edit-testimonial"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Edit').'"></i></a>';
                }else{
                    $action .= '~';
                }
                if(Auth::user()->can('Ticket Delete')){
                    $action .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-sm action-btns" id="show-delete" ><i class="feather feather-trash-2 text-danger" data-id="'.$row->id.'" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Delete') .'"></i></a>';
                }else{
                    $action .= '~';
                }

                return $action;
            })
            ->rawColumns(['serial', 'id' ,'custname', 'mobilenumber', 'status', 'assignedTo','action'])// Ensure HTML is rendered as raw HTML
            ->make(true);
    }

    public function totalassignedgetData(Request $request)
    {
        $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
        ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
        ->leftJoin('customers','customers.id','tickets.cust_id')
        ->leftJoin('ticket_customfields', function ($join) {
            $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                 ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
        })
        ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
        ->latest('tickets.updated_at');

        $query->where(function ($q) {
            $q->where(function ($subQuery) {
                $subQuery->whereNotNull('tickets.myassignuser_id')
                         ->WhereNull('tickets.selfassignuser_id');
            })
            ->orWhere(function ($subQuery) {
                $subQuery->whereNull('tickets.myassignuser_id')
                         ->WhereNotNull('tickets.selfassignuser_id');
            })
            ->orWhere(function ($subQuery) {
                $subQuery->WhereNotNull('tickets.myassignuser_id')
                         ->WhereNotNull('tickets.selfassignuser_id');
            });
        });


        // if ($request->has('search') && !empty($request->search)) {
        //     $searchValue = '%' . $request->search['value'] . '%';
        //     $query->where(function ($q) use ($searchValue) {
        //         $q->where('tickets.subject', 'like', $searchValue)
        //         ->orWhere('ticket_customfields.values', 'like', $searchValue)
        //         ->orWhere('tickets.ticket_id', 'like', $searchValue);
        //     });
        // }

        if ($request->has('search') && !empty($request->search)) {
            $searchValue = '%' . $request->search['value'] . '%';
            $query->where(function ($q) use ($searchValue) {
                $q->where('tickets.subject', 'like', $searchValue)
                ->orWhere('ticket_customfields.values', 'like', $searchValue)
                ->orWhere('tickets.ticket_id', 'like', $searchValue)
                ->orWhere (function ($qs) use ($searchValue) {
                    $qs->where('customers.firstname', 'like', $searchValue)
                        ->orWhere('customers.lastname', 'like', $searchValue);
                });
            });
        }

        $query->get();

        return DataTables::of($query)
            ->addColumn('serial', function ($row) {
                return '';
            })
            ->addColumn('id', function ($row) {
                $show_ticket_id = $row->ticket_id;
                $html = '<a href="ticket-view/' . $show_ticket_id .'" class="fs-14 d-block font-weight-semibold">' .$row->subject . '</a>
                <ul class="fs-13 font-weight-normal d-flex custom-ul">
                    <li class="pe-2 text-muted">#' . $show_ticket_id .'</span>
                    <li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Date').'"><i class="fe fe-calendar me-1 fs-14"></i> '.$row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')).'</li>';

                    if($row->priority != null)
                        if($row->priority == "Low")
                            $html .= '<li class="ps-5 pe-2 preference preference-low" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "High")
                            $html .= '<li class="ps-5 pe-2 preference preference-high" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "Critical")
                             $html .= '<li class="ps-5 pe-2 preference preference-critical" data-bs-toggle="tooltip" data-bs-placement="top" title="' .lang('Priority') . '"> '.lang($row->priority) . '</li>';

                        else
                            $html .= '<li class="ps-5 pe-2 preference preference-medium" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">' .lang($row->priority) .'</li>';
                    else
                        $html .= '~';

                    if($row->category_id != null)
                        if($row->category != null)
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.Str::limit($row->category->name, '40') .'</li>';
                        else
                            $html .= '~';
                    else
                        $html .= '~';

                    if($row->last_reply == null)
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'. lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' . $row->created_at->diffForHumans() .'</li>';
                    else
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' .$row->last_reply->diffForHumans() .'</li>';

                    if($row->purchasecodesupport != null)
                        if($row->purchasecodesupport == 'Supported')
                            $html .= '<li class="px-2 text-success font-weight-semibold">' . lang('Support Active') .'</li>';

                        if($row->purchasecodesupport == 'Expired')
                            $html .= '<li class="px-2 text-danger-dark font-weight-semibold">' .lang('Support Expired') .'</li>';

                $html .= '</ul>';
                return $html;
            })
            ->addColumn('custname', function ($row) {
                return $row->cust->username . ' (' . lang($row->cust->userType) . ')';
            })
            ->addColumn('mobilenumber', function ($row) {
                $mobileNo = $row->values;
                return $mobileNo;
            })
            ->addColumn('status', function ($row) {
                $status = '';
                if($row->status == "New")
                    $status = '<span class="badge badge-burnt-orange">' .lang($row->status) .'</span>';

                elseif($row->status == "Re-Open")
                    $status = '<span class="badge badge-teal">' . lang($row->status) .'</span>';

                elseif($row->status == "Inprogress")
                    $status = '<span class="badge badge-info">' . lang($row->status) .'</span>';

                elseif($row->status == "On-Hold")
                    $status = '<span class="badge badge-warning">' . lang($row->status) .'</span>';

                else
                    $status = '<span class="badge badge-danger">' . lang($row->status) .'</span>';

                return $status;
            })
            ->addColumn('assignedTo', function ($row) {
                $assignedTo = '';
                if(Auth::user()->can('Ticket Assign')){
                    if($row->status == 'Suspend' || $row->status == 'Closed'){
                        $assignedTo .= '<div class="btn-group">';
                            if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" disabled>'.lang('Multi Assign') .' <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="' .$row->id .'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>{{$row->selfassign->name}} (self) <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="'.$row->id.'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign').'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }else{
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>'.lang('Assign').'<span class="caret"></span></button>';
                            }
                        $assignedTo .= '</div>';
                    }else{
                        if($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id == null){
                            $assignedTo .= '<div class="btn-group">';
                            $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign').' <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">'.lang('Assign').' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="'.$row->id.'">'.lang('Self Assign').'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                        }else{
                             $assignedTo .= '<div class="btn-group">';
                                if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .'<span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Multi Assign') .'<span class="caret"></span></button>';
                                        $assignedTo .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .' <span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.$row->selfassign->name .' (self) <span class="caret"></span></button>
                                        <a href="javascript:void(0)" data-id="' .$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="' .lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }else{
                                    $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">' . lang('Assign') .' <span class="caret"></span></button>';
                                }

                               $assignedTo .= '<ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">' .lang('Assign') .' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="' .$row->id .'">'.lang('Self Assign') .'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>';
                            $assignedTo .= '</div>';
                        }
                    }
                }

                return $assignedTo;
            })
            ->addColumn('action', function ($row) {
                $action = '';
                if(Auth::user()->can('Ticket Edit')){
                    $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) .'" class="btn btn-sm action-btns edit-testimonial"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Edit').'"></i></a>';
                }else{
                    $action .= '~';
                }
                if(Auth::user()->can('Ticket Delete')){
                    $action .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-sm action-btns" id="show-delete" ><i class="feather feather-trash-2 text-danger" data-id="'.$row->id.'" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Delete') .'"></i></a>';
                }else{
                    $action .= '~';
                }

                return $action;
            })
            ->rawColumns(['serial', 'id' ,'custname', 'mobilenumber', 'status', 'assignedTo','action'])// Ensure HTML is rendered as raw HTML
            ->make(true);
    }


    public function adminallassignedtickets()
    {

        // $oneMonthBefore = date('Y-m-d h:i:s', strtotime('-10 days'));
        // //$allassignedtickets = Ticket::latest('updated_at')->get();
        // $allassignedtickets = Ticket::where('created_at', '>', $oneMonthBefore)->latest('updated_at')->get();
        // $data['allassignedtickets'] = $allassignedtickets;

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.superadmindashboard.allassignedtickets')->with($data);
    }

    public function recenttickets()
    {

        if(Auth::user()->dashboard == 'Admin'){
            return $this->adminrecentticket();
        }
        if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            return $this->employeerecentticket();
        }

    }

    public function adminrecentticket()
    {
        // $recenttickets = Ticket::whereIn('status', ['New'])->latest('updated_at')->get();
        // $data['recenttickets'] = $recenttickets;

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.superadmindashboard.recenttickets')->with($data);
    }


    public function employeerecentticket()
    {

        $groupexists = Groupsusers::where('users_id', Auth::id())->exists();

        // if there in group get group tickets
        if($groupexists){

            $recenttickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                //->whereIn('tickets.status', ['New'])
                ->whereNotNull('groups_users.users_id')
                ->where('groups_users.users_id', Auth::id())
                ->latest('tickets.updated_at')
                ->get();
                $data['gtickets'] = $recenttickets;

            $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
            $data['ticketnote'] = $ticketnote;


        }
        // If no there in group we get the all tickets
        else{

            $recenttickets = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            //->whereIn('tickets.status', ['New'])
            ->whereNull('groups_users.users_id')
            ->latest('tickets.updated_at')
            ->get();
            $data['gtickets'] = $recenttickets;

            $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
            $data['ticketnote'] = $ticketnote;

        }

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.assignedtickets.recenttickets')->with($data);

    }

    public function markNotification(Request $request)
    {
        auth()->user()
            ->unreadNotifications
            ->when($request->input('id'), function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })
            ->markAsRead();

        return response()->noContent();
    }


    public function autorefresh(Request $request, $id)
    {
        $calID = User::with('usetting')->find($id);
        if($calID->usetting == null){
            $usersettings = new usersettings();
            $usersettings->users_id = $request->id;
            $usersettings->ticket_refresh = $request->status;
            $usersettings->save();
        }
        else{
            $calID->usetting->ticket_refresh = $request->status;
            $calID->usetting->save();
        }

        return response()->json(['code'=>200, 'success'=> lang('Updated successfully', 'alerts')], 200);

    }


    public function summernoteimageupload(Request $request)
    {
        $files = $request->file('image');

        $destinationPath = public_path() . "" . '/uploads/data/'; // upload path
        $profileImage = date('YmdHis') . "." . $files->getClientOriginalExtension();
        $path = $files->move($destinationPath, $profileImage);

        $destinationPath1 = url('/').'/uploads/data/' .$profileImage;
        return response()->json(['code'=>200, 'data' => $destinationPath1,  ], 200);
    }


    public function Notificationview($id)
   {
        $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $data['notifications'] = $notification;

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.notification.viewnotification')->with($data);
   }

//    public function notifystatus(Request $request)
//    {
//        $status = $request->statusnotify;
//        if(!$status){
//             $notifications = auth()->user()->notifications()->paginate('10')->groupBy(function($date) {
//                 return \Carbon\Carbon::parse($date->created_at)->format('Y-m-d');
//             });
//        }
//        else{
//             $notifications =  auth()->user()->notifications()->whereIn('data->status', $status)->paginate('10')->groupBy(function($date) {
//                 return \Carbon\Carbon::parse($date->created_at)->format('Y-m-d');
//             });
//        }

//        $title = Apptitle::first();
//         $data['title'] = $title;

//         $footertext = Footertext::first();
//         $data['footertext'] = $footertext;

//         $seopage = Seosetting::first();
//         $data['seopage'] = $seopage;

//        $view = view('admin.notificationpageinclude',compact('notifications','title', 'footertext', 'seopage'))->render();
//        return response()->json(['html'=>$view]);
//    }




// Updated NotifySearch Function

public function notifystatus(Request $request)
{
    $status = $request->statusnotify;
    $user = auth()->user();

    // Base query
    $notificationsQuery = $user->notifications()->orderBy('created_at', 'desc');

    // If user is an Agent, check if their ID is inside the array of assigned users
    if ($user->hasRole('Agent')) {
        $notificationsQuery->where('data->ticketassign', 'yes')
                           ->whereJsonContains('data->toassignuser_id', $user->id);
    }

    // Filter by status (if any selected)
    if ($status) {
        $notificationsQuery->whereIn('data->status', $status);
    }

    // Get paginated and group by date
    $notifications = $notificationsQuery->paginate(10)->getCollection()->groupBy(function ($date) {
        return \Carbon\Carbon::parse($date->created_at)->format('Y-m-d');
    });

    // Metadata
    $title = Apptitle::first();
    $footertext = Footertext::first();
    $seopage = Seosetting::first();

    // Render view
    $view = view('admin.notificationpageinclude', compact('notifications', 'title', 'footertext', 'seopage'))->render();

    return response()->json(['html' => $view]);
}








   public function notifydelete(Request $request)
   {
       $id = $request->id;

       $notificationsdelete = auth()->user()->notifications()->find($id);
       $notificationsdelete->delete();

       return response()->json(['success'=> lang('Deleted successfully', 'alerts'),200]);
   }

   public function markallnotify()
   {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->noContent();
   }

   public function clearcache()
   {
       Artisan::call('optimize:clear');

        return response()->json(['success'=> lang('Cache Clear Successfull', 'alerts')]);
   }

   public function suspendedtickets()
   {
        //$data['suspendedtickets'] = Ticket::where('status', 'Suspend')->latest('updated_at')->get();

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.superadmindashboard.suspendedtickets')->with($data);
   }

   public function suspendedticketsdata(Request $request)
    {

        if(Auth::user()->dashboard == 'Admin'){
            $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
            ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
            ->leftJoin('customers','customers.id','tickets.cust_id')
            ->leftJoin('ticket_customfields', function ($join) {
                $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                     ->where('ticket_customfields.fieldnames', '=', 'Mobile no.');
            })
            ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
            ->whereIn('tickets.status', ['Suspend'])
            ->latest('tickets.updated_at');
        }else if(Auth::user()->dashboard == 'Employee' || Auth::user()->dashboard == null){
            $groupexists = Groupsusers::where('users_id', Auth::id())->exists();
            if($groupexists){
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                ->leftJoin('customers','customers.id','tickets.cust_id')
                ->leftJoin('ticket_customfields', function ($join) {
                    $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                        ->where('ticket_customfields.fieldnames', '=', "Mobile no.");
                })
                ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                ->whereIn('tickets.status', ['Suspend'])
                ->whereNotNull('groups_users.users_id')
                ->where('groups_users.users_id', Auth::id())
                ->where(function ($q) {
                    $q->where('ticketassignchildren.toassignUser_id', Auth::id())
                    ->orWhere('tickets.users_id', Auth::id())
                    ->orWhere('tickets.myassignuser_id', Auth::id());
                })
                ->latest('tickets.updated_at');
            }
            else{
                $query = Ticket::select('tickets.*',"groups_categories.group_id","groups_users.users_id", "ticket_customfields.values")
                    ->leftJoin('groups_categories','groups_categories.category_id','tickets.category_id')
                    ->leftJoin('ticketassignchildren', 'tickets.id', 'ticketassignchildren.ticket_id')
                    ->leftJoin('customers','customers.id','tickets.cust_id')
                    ->leftJoin('ticket_customfields', function ($join) {
                        $join->on('ticket_customfields.ticket_id', '=', 'tickets.id')
                            ->where('ticket_customfields.fieldnames', '=', "Mobile no.");
                    })
                    ->leftJoin('groups_users','groups_users.groups_id','groups_categories.group_id')
                    ->whereIn('tickets.status', ['Suspend'])
                    ->whereNull('groups_users.users_id')
                    ->where(function ($q) {
                        $q->where('tickets.myassignuser_id', Auth::id())
                        ->orWhere('tickets.user_id', Auth::id())
                        ->orWhere('tickets.selfassignuser_id', Auth::id());
                    });
            }
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchValue = '%' . $request->search['value'] . '%';
            $query->where(function ($q) use ($searchValue) {
                $q->where('tickets.subject', 'like', $searchValue)
                ->orWhere('ticket_customfields.values', 'like', $searchValue)
                ->orWhere('tickets.ticket_id', 'like', $searchValue)
                ->orWhere (function ($qs) use ($searchValue) {
                    $qs->where('customers.firstname', 'like', $searchValue)
                        ->orWhere('customers.lastname', 'like', $searchValue);
                });
            });
        }

        $query->get();

        return DataTables::of($query)
            ->addColumn('serial', function ($row) {
                return '';
            })
            ->addColumn('id', function ($row) {
                $html = '<a href="ticket-view/' . $row->ticket_id .'" class="fs-14 d-block font-weight-semibold">' .$row->subject . '</a>
                <ul class="fs-13 font-weight-normal d-flex custom-ul">
                    <li class="pe-2 text-muted">#' . $row->ticket_id .'</span>
                    <li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Date').'"><i class="fe fe-calendar me-1 fs-14"></i> '.$row->created_at->timezone(Auth::user()->timezone)->format(setting('date_format')).'</li>';

                    if($row->priority != null)
                        if($row->priority == "Low")
                            $html .= '<li class="ps-5 pe-2 preference preference-low" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "High")
                            $html .= '<li class="ps-5 pe-2 preference preference-high" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">'.lang($row->priority) .'</li>';

                        elseif($row->priority == "Critical")
                             $html .= '<li class="ps-5 pe-2 preference preference-critical" data-bs-toggle="tooltip" data-bs-placement="top" title="' .lang('Priority') . '"> '.lang($row->priority) . '</li>';

                        else
                            $html .= '<li class="ps-5 pe-2 preference preference-medium" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Priority') .'">' .lang($row->priority) .'</li>';
                    else
                        $html .= '~';

                    if($row->category_id != null)
                        if($row->category != null)
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.Str::limit($row->category->name, '40') .'</li>';
                        else
                            $html .= '~';
                    else
                        $html .= '~';

                    if($row->last_reply == null)
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'. lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' . $row->created_at->diffForHumans() .'</li>';
                    else
                        $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Last Replied') .'"><i class="fe fe-clock me-1 fs-14"></i>' .$row->last_reply->diffForHumans() .'</li>';

                    if($row->purchasecodesupport != null)
                        if($row->purchasecodesupport == 'Supported')
                            $html .= '<li class="px-2 text-success font-weight-semibold">' . lang('Support Active') .'</li>';

                        if($row->purchasecodesupport == 'Expired')
                            $html .= '<li class="px-2 text-danger-dark font-weight-semibold">' .lang('Support Expired') .'</li>';

                $html .= '</ul>';
                return $html;
            })
            ->addColumn('custname', function ($row) {
                return $row->cust->username . ' (' . lang($row->cust->userType) . ')';
            })
            ->addColumn('mobilenumber', function ($row) {
                $mobileNo = $row->values;
                return $mobileNo;
            })
            ->addColumn('status', function ($row) {
                $status = '';
                if($row->status == "New")
                    $status = '<span class="badge badge-burnt-orange">' .lang($row->status) .'</span>';

                elseif($row->status == "Re-Open")
                    $status = '<span class="badge badge-teal">' . lang($row->status) .'</span>';

                elseif($row->status == "Inprogress")
                    $status = '<span class="badge badge-info">' . lang($row->status) .'</span>';

                elseif($row->status == "On-Hold")
                    $status = '<span class="badge badge-warning">' . lang($row->status) .'</span>';

                else
                    $status = '<span class="badge badge-danger">' . lang($row->status) .'</span>';

                return $status;
            })
            ->addColumn('assignedTo', function ($row) {
                $assignedTo = '';
                if(Auth::user()->can('Ticket Assign')){
                    if($row->status == 'Suspend' || $row->status == 'Closed'){
                        $assignedTo .= '<div class="btn-group">';
                            if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" disabled>'.lang('Multi Assign') .' <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="' .$row->id .'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>{{$row->selfassign->name}} (self) <span class="caret"></span></button>';
                                $assignedTo .= '<button data-id="'.$row->id.'" class="btn btn-outline-primary" id="btnremove" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign').'" aria-label="Unassign"><i class="fe fe-x"></i></button>';
                            }else{
                                $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"  disabled>'.lang('Assign').'<span class="caret"></span></button>';
                            }
                        $assignedTo .= '</div>';
                    }else{
                        if($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id == null){
                            $assignedTo .= '<div class="btn-group">';
                            $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign').' <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">'.lang('Assign').' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="'.$row->id.'">'.lang('Self Assign').'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>
                            </div>';
                        }else{
                             $assignedTo .= '<div class="btn-group">';
                                if($row->ticketassignmutliples->isNotEmpty() && $row->selfassignuser_id == null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .'<span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Multi Assign') .'<span class="caret"></span></button>';
                                        $assignedTo .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="'.lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }elseif($row->ticketassignmutliples->isEmpty() && $row->selfassignuser_id != null){
                                    if($row->ticketassignmutliples->isEmpty() && $row->selfassign == null){
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.lang('Assign') .' <span class="caret"></span></button>';
                                    }else{
                                        $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">'.$row->selfassign->name .' (self) <span class="caret"></span></button>
                                        <a href="javascript:void(0)" data-id="' .$row->id .'" class="btn btn-outline-primary btn-sm" id="btnremove" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="' .lang('Unassign') .'" aria-label="Unassign"><i class="fe fe-x"></i></a>';
                                    }
                                }else{
                                    $assignedTo .= '<button class="btn btn-outline-primary dropdown-toggle btn-sm" data-bs-toggle="dropdown">' . lang('Assign') .' <span class="caret"></span></button>';
                                }

                               $assignedTo .= '<ul class="dropdown-menu" role="menu">
                                    <li class="dropdown-plus-title">' .lang('Assign') .' <b aria-hidden="true" class="fa fa-angle-up"></b></li>
                                    <li>
                                        <a href="javascript:void(0);" id="selfassigid" data-id="' .$row->id .'">'.lang('Self Assign') .'</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" data-id="'.$row->id.'" id="assigned">
                                        '.lang('Other Assign').'
                                        </a>
                                    </li>
                                </ul>';
                            $assignedTo .= '</div>';
                        }
                    }
                }

                return $assignedTo;
            })
            ->addColumn('action', function ($row) {
                $action = '';
                if(Auth::user()->can('Ticket Edit')){
                    $action .= '<a href="' . url('admin/ticket-view/' . $row->ticket_id) .'" class="btn btn-sm action-btns edit-testimonial"><i class="feather feather-eye text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Edit').'"></i></a>';
                }else{
                    $action .= '~';
                }
                if(Auth::user()->can('Ticket Delete')){
                    $action .= '<a href="javascript:void(0)" data-id="'.$row->id .'" class="btn btn-sm action-btns" id="show-delete" ><i class="feather feather-trash-2 text-danger" data-id="'.$row->id.'" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Delete') .'"></i></a>';
                }else{
                    $action .= '~';
                }

                return $action;
            })
            ->rawColumns(['serial', 'id' ,'custname', 'mobilenumber', 'status', 'assignedTo','action'])// Ensure HTML is rendered as raw HTML
            ->make(true);
    }
// move to dashboard and value of emailticketfile will be null
public function moveToDashboard(Request $request)
{
    // 🔍 Debug log: will appear in storage/logs/laravel.log
    \Log::info('Ticket move request', ['ids' => $request->ticket_ids]);

    // Optional: Validation (recommended)
    $request->validate([
        'ticket_ids' => 'required|array',
        'ticket_ids.*' => 'integer|exists:tickets,id'
    ]);

    Ticket::whereIn('id', $request->ticket_ids)->update([
        'emailticketfile' => null
    ]);

    return response()->json(['status' => 'success']);
}

// send follow-up mail

public function saveFollowup(Request $request)
{
    try {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'note' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        Mail::to($user->email)->send(new FollowupMail($user->name, $request->ticket_id, $request->note));

        return response()->json(['success' => true, 'message' => 'Follow up saved and email sent.']);
    } catch (\Exception $e) {
        \Log::error('Followup error: '.$e->getMessage());
        return response()->json(['success' => false, 'message' => 'Server Error', 'error' => $e->getMessage()], 500);
    }
}

public function assignTicket(Request $request)
{
    $request->validate([
        'ticket_id' => 'required|exists:tickets,id',
        'user_id' => 'required|exists:users,id',
    ]);

    $ticket = Ticket::findOrFail($request->ticket_id);
    $ticket->selfassignuser_id = $request->user_id;
    $ticket->save();

    return response()->json(['success' => true]);
}




}
