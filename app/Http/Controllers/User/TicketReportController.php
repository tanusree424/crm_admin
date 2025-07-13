<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seosetting;
use App\Models\Apptitle;
use App\Models\Footertext;
use App\Models\Ticket\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Models\Pages;
class TicketReportController extends Controller
{
public function index()
{
    $customer = Auth::guard('customer')->user();

    // Meta data
    $title = DB::table('apptitles')->first();
    $footertext = DB::table('footertexts')->first();
    $seopage = DB::table('seosettings')->first();
    $page = DB::table('pages')->get();

    // Count tickets by status
    $statusCounts = DB::select("
        SELECT status, COUNT(*) as count
        FROM tickets
        WHERE cust_id = ?
        GROUP BY status
    ", [$customer->id]);

    $statusData = collect($statusCounts)->pluck('count', 'status')->toArray();
    $priorityData = DB::table('tickets')
        ->select('priority', DB::raw('count(*) as total'))
        ->where('cust_id', $customer->id)
        ->groupBy('priority')
        ->pluck('total', 'priority')
        ->toArray();

    $priorities = ['Low', 'Medium', 'High', 'Critical'];
    foreach ($priorities as $priority) {
        if (!isset($priorityData[$priority])) {
            $priorityData[$priority] = 0;
        }
    }
    $ticketCategories = DB::table('categories')
    ->join('tickets', 'categories.id', '=', 'tickets.category_id')
    ->select('categories.id', 'categories.name')
    ->where('tickets.cust_id', Auth::guard('customer')->id()) // limit to current customer
    ->distinct()
    ->get();

    return view('user.auth.TickertReport', compact(
        'title',
        'footertext',
        'seopage',
        'page',
        'statusData',
        'priorityData',
        'ticketCategories'

    ));
}



// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Response;
public function exportByCategory(Request $request)
{
    if (!$request->has('download') || !$request->has('category_id')) {
        abort(404); // Prevent access without proper params
    }

    $categoryId = $request->category_id;
    $userId = Auth::guard('customer')->id();

    $tickets = DB::table('tickets')
        ->where('cust_id', $userId)
        ->where('category_id', $categoryId)
        ->get();

    if ($tickets->isEmpty()) {
        return back()->with('error', 'No tickets found for this category.');
    }

    $filename = "tickets_category_{$categoryId}.csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    $columns = [
        'id', 'ticket_id', 'subject', 'status', 'priority',
        'created_at', 'updated_at'
    ];

    $callback = function () use ($tickets, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($tickets as $ticket) {
            $row = [];
            foreach ($columns as $col) {
                $row[] = $ticket->$col;
            }
            fputcsv($file, $row);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}


}
?>
