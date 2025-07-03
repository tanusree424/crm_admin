<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Apptitle;
use App\Models\Footertext;
use App\Models\Seosetting;
use App\Models\Pages;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\Comment;
use App\Models\Ticket\Category;
use App\Models\Groupsusers;
use Auth;
use DB;
use DataTables;
use App\Models\tickethistory;
use App\Models\Customer;
use App\Models\Material;
use App\Models\MaterialGroup1;
use App\Models\MaterialGroup2;

class AdminTicketViewController extends Controller
{
    public function customerprevioustickets($cust_id)
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $users = Customer::find($cust_id);
        $data['users'] = $users;

        $total = Ticket::where('cust_id', $cust_id)->latest('updated_at')->get();
        $data['total'] = $total;

        $materials = Material::select('id','material_code','material_name')->get();
        $data['materials'] = $materials;
         $brand = MaterialGroup2::get();
        $data['brand'] = $brand;
        $product_type = MaterialGroup1::get();
        $data['product_type'] = $product_type;

        $custsimillarticket = Ticket::where('cust_id', $cust_id)->latest('updated_at')->get();
        $data['custsimillarticket'] = $custsimillarticket;


        $active = Ticket::where('cust_id', $cust_id)->whereIn('status', ['New', 'Re-Open', 'Inprogress'])->get();
       $data['active'] = $active;

       $closed = Ticket::where('cust_id', $cust_id)->where('status', 'Closed')->get();
        $data['closed'] = $closed;

        $onhold = Ticket::where('cust_id', $cust_id)->where('status', 'On-Hold')->get();
        $data['onhold'] = $onhold;

        return view('admin.viewticket.customerprevioustickets')->with($data);
    }

    public function selfassignticketview()
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        // $selfassignedtickets = Ticket::where('selfassignuser_id', auth()->id())->where('status', '!=' ,'Closed')->where('status', '!=' ,'Suspend')->latest('updated_at')->get();
        // $data['selfassignedtickets'] = $selfassignedtickets;

        // ticket note
        $ticketnote = DB::table('ticketnotes')->pluck('ticketnotes.ticket_id')->toArray();
        $data['ticketnote'] = $ticketnote;


        $selfassignedticketsnew = Ticket::where('selfassignuser_id', auth()->id())->where('status', 'New')->count();
        $data['selfassignedticketsnew'] = $selfassignedticketsnew;

        $selfassignedticketsinprogress = Ticket::where('selfassignuser_id', auth()->id())->where('status', 'Inprogress')->count();
        $data['selfassignedticketsinprogress'] = $selfassignedticketsinprogress;

        $selfassignedticketsonhold = Ticket::where('selfassignuser_id', auth()->id())->where('status', 'On-Hold')->count();
        $data['selfassignedticketsonhold'] = $selfassignedticketsonhold;

        $selfassignedticketsreopen = Ticket::where('selfassignuser_id', auth()->id())->where('status', 'Re-Open')->count();
        $data['selfassignedticketsreopen'] = $selfassignedticketsreopen;

        $selfassignedticketsoverdue = Ticket::where('selfassignuser_id', auth()->id())->where('overduestatus', 'Overdue')->count();
        $data['selfassignedticketsoverdue'] = $selfassignedticketsoverdue;

        $selfassignedticketsclosed = Ticket::where('selfassignuser_id', auth()->id())->where('status', 'Closed')->count();
        $data['selfassignedticketsclosed'] = $selfassignedticketsclosed;

        return view('admin.superadmindashboard.mytickets.selfassignticket')->with($data);
    }

    public function myclosedtickets()
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        // $myclosedbyuser = Ticket::where('closedby_user', auth()->id())->latest('updated_at')->get();
        // $data['myclosedbyuser'] = $myclosedbyuser;

        return view('admin.assignedtickets.myclosedtickets')->with($data);
    }

    public function allmyclosedtickets(Request $request)
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
        ->where('tickets.closedby_user', auth()->id())
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
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.$row->category->name .'</li>';
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

    public function allmyselfassignedTickets(Request $request)
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
        ->where('tickets.selfassignuser_id', auth()->id())
        ->where('tickets.status', '!=' ,'Closed')
        ->where('tickets.status', '!=' ,'Suspend')
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
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.$row->category->name .'</li>';
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

    public function allmysuspendedTickets(Request $request)
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
        ->where('tickets.lastreply_mail', auth()->id())
        ->whereIn('tickets.status', ['Suspend'])
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
                            $html .= '<li class="px-2 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="'.lang('Category') .'"><i class="fe fe-grid me-1 fs-14" ></i>'.$row->category->name .'</li>';
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

    public function tickettrashed()
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $tickettrashed = Ticket::onlyTrashed()->latest('updated_at')->get();
        $data['tickettrashed'] = $tickettrashed;

        return view('admin.assignedtickets.trashedticket')->with($data);
    }

    public function tickettrashedrestore(Request $request, $id)
    {
        $tickettrashedrestore = Ticket::onlyTrashed()->findOrFail($id);
        $commenttrashedrestore = $tickettrashedrestore->comments()->onlyTrashed()->get();

        if (count($commenttrashedrestore) > 0) {

            $commenttrashedrestore->each->restore();

            $tickethistory = new tickethistory();
            $tickethistory->ticket_id = $tickettrashedrestore->id;

            $output = '<div class="d-flex align-items-center">
                <div class="mt-0">
                    <p class="mb-0 fs-12 mb-1">Status
                ';
            if($tickettrashedrestore->ticketnote->isEmpty()){
                if($tickettrashedrestore->overduestatus != null){
                    $output .= '
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->status.'</span>
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->overduestatus.'</span>
                    ';
                }else{
                    $output .= '
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->status.'</span>
                    ';
                }

            }else{
                if($tickettrashedrestore->overduestatus != null){
                    $output .= '
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->status.'</span>
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->overduestatus.'</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                }else{
                    $output .= '
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->status.'</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                }
            }

            $output .= '
                <p class="mb-0 fs-17 font-weight-semibold text-dark">'.Auth::user()->name.'<span class="fs-11 mx-1 text-muted">(Ticket Restore)</span></p>
            </div>
            <div class="ms-auto">
            <span class="float-end badge badge-primary-light">
                <span class="fs-11 font-weight-semibold">'.Auth::user()->getRoleNames()[0].'</span>
            </span>
            </div>

            </div>
            ';
            $tickethistory->ticketactions = $output;
            $tickethistory->save();





            foreach($tickettrashedrestore->ticket_history()->onlyTrashed()->get() as $deletetickethistory)
            {
                $deletetickethistory->restore();
            }


            $tickettrashedrestore->restore();
            return response()->json(['success'=>lang('The ticket was successfully restore.', 'alerts')]);
        }else{


            $tickettrashedrestore->restore();

            $tickethistory = new tickethistory();
            $tickethistory->ticket_id = $tickettrashedrestore->id;

            $output = '<div class="d-flex align-items-center">
                <div class="mt-0">
                    <p class="mb-0 fs-12 mb-1">Status
                ';
            if($tickettrashedrestore->ticketnote->isEmpty()){
                if($tickettrashedrestore->overduestatus != null){
                    $output .= '
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->status.'</span>
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->overduestatus.'</span>
                    ';
                }else{
                    $output .= '
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->status.'</span>
                    ';
                }

            }else{
                if($tickettrashedrestore->overduestatus != null){
                    $output .= '
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->status.'</span>
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->overduestatus.'</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                }else{
                    $output .= '
                    <span class="text-danger font-weight-semibold mx-1">'.$tickettrashedrestore->status.'</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                }
            }

            $output .= '
                <p class="mb-0 fs-17 font-weight-semibold text-dark">'.Auth::user()->name.'<span class="fs-11 mx-1 text-muted">(Ticket Restore)</span></p>
            </div>
            <div class="ms-auto">
            <span class="float-end badge badge-primary-light">
                <span class="fs-11 font-weight-semibold">'.Auth::user()->getRoleNames()[0].'</span>
            </span>
            </div>

            </div>
            ';
            $tickethistory->ticketactions = $output;
            $tickethistory->save();





            foreach($tickettrashedrestore->ticket_history()->onlyTrashed()->get() as $deletetickethistory)
            {
                $deletetickethistory->restore();
            }

            return response()->json(['success'=> lang('The ticket was successfully restore.', 'alerts')]);

        }
    }

    public function tickettrasheddestroy($id)
    {
        $tickettrasheddelete = Ticket::onlyTrashed()->findOrFail($id);
        $commenttrasheddelete = $tickettrasheddelete->comments()->onlyTrashed()->get();


        if (count($commenttrasheddelete) > 0) {
            $media = $tickettrasheddelete->getMedia('ticket');

            foreach ($media as $medias) {

                    $medias->forceDelete();

            }
            $medias = $tickettrasheddelete->comments()->onlyTrashed()->get();

            foreach ($medias as $mediass) {
                foreach($mediass->getMedia('comments') as $mediasss){

                    $mediasss->forceDelete();
                }

            }
            $commenttrasheddelete->each->forceDelete();

            foreach($tickettrasheddelete->ticket_history()->onlyTrashed()->get() as $deletetickethistory)
            {
                $deletetickethistory->forceDelete();
            }
            $tickettrasheddelete->forceDelete();
            return response()->json(['success'=>lang('The ticket was successfully deleted.', 'alerts')]);
        }else{

            $media = $tickettrasheddelete->getMedia('ticket');

            foreach ($media as $medias) {

                    $medias->forceDelete();

            }

            foreach($tickettrasheddelete->ticket_history()->onlyTrashed()->get() as $deletetickethistory)
            {
                $deletetickethistory->forceDelete();
            }
            $tickettrasheddelete->forceDelete();

            return response()->json(['success'=> lang('The ticket was successfully deleted.', 'alerts')]);

        }
    }


    public function tickettrashedview($id)
    {
        $tickettrashedview = Ticket::onlyTrashed()->findOrFail($id);
        $data['tickettrashedview'] = $tickettrashedview;

        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        return view('admin.assignedtickets.trashedticketview')->with($data);
    }


    public function alltrashedticketrestore(Request $request)
    {

        $id_array = $request->input('id');

        $sendmails = Ticket::onlyTrashed()->whereIn('id', $id_array)->get();

        foreach($sendmails as $tickettrashedrestoreall){
            $commenttrashedrestorealls = $tickettrashedrestoreall->comments()->onlyTrashed()->get();
            foreach($commenttrashedrestorealls as $commenttrashedrestoreall){
                    $commenttrashedrestoreall->restore();
            }
            $tickettrashedrestoreall->restore();

        }
        return response()->json(['success'=> lang('The ticket was successfully restore.', 'alerts')]);

    }

    public function alltrashedticketdelete(Request $request)
    {
        $id_array = $request->input('id');

        $sendmails = Ticket::onlyTrashed()->whereIn('id', $id_array)->get();

        foreach($sendmails as $tickettrasheddeleteeall){

            $commenttrasheddeleteall = $tickettrasheddeleteeall->comments()->onlyTrashed()->get();


            if (count($commenttrasheddeleteall) > 0) {
                $media = $tickettrasheddeleteeall->getMedia('ticket');

                foreach ($media as $medias) {

                        $medias->forceDelete();

                }

                foreach ($commenttrasheddeleteall as $mediass) {
                    foreach($mediass->getMedia('comments') as $mediasss){

                        $mediasss->forceDelete();
                    }

                    $mediass->forceDelete();
                }

                foreach($tickettrasheddeleteeall->ticket_history()->onlyTrashed()->get() as $deletetickethistory)
                {
                    $deletetickethistory->forceDelete();
                }


                $sendmails->each->forceDelete();
                return response()->json(['success'=>lang('The ticket was successfully deleted.', 'alerts')]);
            }else{

                $media = $tickettrasheddeleteeall->getMedia('ticket');

                foreach ($media as $medias) {

                    $medias->forceDelete();

                }

                foreach($tickettrasheddeleteeall->ticket_history()->onlyTrashed()->get() as $deletetickethistory)
                {
                    $deletetickethistory->forceDelete();
                }


                $sendmails->each->forceDelete();

                return response()->json(['success'=> lang('The ticket was successfully deleted.', 'alerts')]);

            }

        }
    }


    public function suspend(Request $request)
    {
        if($request->unsuspend == 'Inprogress'){
            $ticketsuspend = Ticket::find($request->ticket_id);
            $ticketsuspend->status = 'Inprogress';
            $ticketsuspend->lastreply_mail = Auth::id();
            $ticketsuspend->update();

            $tickethistory = new tickethistory();
            $tickethistory->ticket_id = $ticketsuspend->id;

            $output = '<div class="d-flex align-items-center">
                <div class="mt-0">
                    <p class="mb-0 fs-12 mb-1">Status
                ';
            if($ticketsuspend->ticketnote->isEmpty()){
                if($ticketsuspend->overduestatus != null){
                    $output .= '
                    <span class="text-burnt-orange font-weight-semibold mx-1">'.$ticketsuspend->status.'</span>
                    <span class="text-danger font-weight-semibold mx-1">'.$ticketsuspend->overduestatus.'</span>
                    ';
                }else{
                    $output .= '
                    <span class="text-burnt-orange font-weight-semibold mx-1">'.$ticketsuspend->status.'</span>
                    ';
                }

            }else{
                if($ticketsuspend->overduestatus != null){
                    $output .= '
                    <span class="text-burnt-orange font-weight-semibold mx-1">'.$ticketsuspend->status.'</span>
                    <span class="text-danger font-weight-semibold mx-1">'.$ticketsuspend->overduestatus.'</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                }else{
                    $output .= '
                    <span class="text-burnt-orange font-weight-semibold mx-1">'.$ticketsuspend->status.'</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                }
            }

            $output .= '
                <p class="mb-0 fs-17 font-weight-semibold text-dark">'.Auth::user()->name.'<span class="fs-11 mx-1 text-muted">(Unsuspended Ticket)</span></p>
            </div>
            <div class="ms-auto">
            <span class="float-end badge badge-primary-light">
                <span class="fs-11 font-weight-semibold">'.Auth::user()->getRoleNames()[0].'</span>
            </span>
            </div>

            </div>
            ';
            $tickethistory->ticketactions = $output;
            $tickethistory->save();



        }
        else{
            $ticketsuspend = Ticket::find($request->ticket_id);
            $ticketsuspend->status = 'Suspend';
            $ticketsuspend->lastreply_mail = Auth::id();
            $ticketsuspend->update();


            $tickethistory = new tickethistory();
            $tickethistory->ticket_id = $ticketsuspend->id;

            $output = '<div class="d-flex align-items-center">
                <div class="mt-0">
                    <p class="mb-0 fs-12 mb-1">Status
                ';
            if($ticketsuspend->ticketnote->isEmpty()){
                if($ticketsuspend->overduestatus != null){
                    $output .= '
                    <span class="text-burnt-orange font-weight-semibold mx-1">'.$ticketsuspend->status.'</span>
                    <span class="text-danger font-weight-semibold mx-1">'.$ticketsuspend->overduestatus.'</span>
                    ';
                }else{
                    $output .= '
                    <span class="text-burnt-orange font-weight-semibold mx-1">'.$ticketsuspend->status.'</span>
                    ';
                }

            }else{
                if($ticketsuspend->overduestatus != null){
                    $output .= '
                    <span class="text-burnt-orange font-weight-semibold mx-1">'.$ticketsuspend->status.'</span>
                    <span class="text-danger font-weight-semibold mx-1">'.$ticketsuspend->overduestatus.'</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                }else{
                    $output .= '
                    <span class="text-burnt-orange font-weight-semibold mx-1">'.$ticketsuspend->status.'</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                }
            }
            $output .= '
                <p class="mb-0 fs-17 font-weight-semibold text-dark">'.Auth::user()->name.'<span class="fs-11 mx-1 text-muted">(suspended Ticket)</span></p>
            </div>
            <div class="ms-auto">
            <span class="float-end badge badge-primary-light">
                <span class="fs-11 font-weight-semibold">'.Auth::user()->getRoleNames()[0].'</span>
            </span>
            </div>

            </div>
            ';

            $tickethistory->ticketactions = $output;
            $tickethistory->save();


        }


        return response()->json(['success' => lang('Update Successfully')]);
    }


    public function mysuspendtickets()
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        // $mysuspendtickets = Ticket::where('status', 'Suspend')->where('lastreply_mail', auth()->id())->latest('updated_at')->get();
        // $data['mysuspendtickets'] = $mysuspendtickets;

        return view('admin.assignedtickets.mysuspendtickets')->with($data);
    }

    public function allactiveinprogresstickets()
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $allactiveinprogresstickets = Ticket::where('status', 'Inprogress')->get();
        $data['allactiveinprogresstickets'] = $allactiveinprogresstickets;

        return view('admin.superadmindashboard.activetickets.activeinprogressticket')->with($data);
    }

    public function allactivereopentickets()
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $allactivereopentickets = Ticket::whereIn('status', ['Re-Open'])->get();
        $data['allactivereopentickets'] = $allactivereopentickets;

        return view('admin.superadmindashboard.activetickets.activereopenticket')->with($data);
    }

    public function allactiveonholdtickets()
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $allactiveonholdtickets = Ticket::whereIn('status', ['On-Hold'])->get();
        $data['allactiveonholdtickets'] = $allactiveonholdtickets;

        return view('admin.superadmindashboard.activetickets.activeonholdticket')->with($data);
    }

    public function allactiveassignedtickets()
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;

        $allactiveassignedtickets = Ticket::whereIn('status', ['Re-Open','Inprogress','On-Hold'])->leftjoin('ticketassignchildren', 'ticketassignchildren.ticket_id', 'tickets.id')->where(function($r){
            $r->whereNotNull('toassignuser_id')
            ->orWhereNotNull('selfassignuser_id');
        })->get();
        $data['allactiveassignedtickets'] = $allactiveassignedtickets;

        return view('admin.superadmindashboard.activetickets.activeassignedticket')->with($data);
    }

    public function tickethistory($id)
    {
        $title = Apptitle::first();
        $data['title'] = $title;

        $footertext = Footertext::first();
        $data['footertext'] = $footertext;

        $seopage = Seosetting::first();
        $data['seopage'] = $seopage;

        $post = Pages::all();
        $data['page'] = $post;


        $ticket = Ticket::where('ticket_id', $id)->firstOrFail();
        $data['ticket'] = $ticket;
        return view('admin.tickethistory.index')->with($data);
    }

}
