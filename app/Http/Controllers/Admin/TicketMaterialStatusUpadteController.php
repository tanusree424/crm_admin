<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class TicketMaterialStatusUpadteController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
// public function ticketData()
// {
//     $ticketdata = DB::select('SELECT * FROM tickets WHERE ticket_id=?  ');

// }
public function checkMaterialStatus(Request $request)
{
    $request->validate([
        'ticket_id' => 'required|string',
    ]);

    try {
        $ticket = DB::table('tickets')->where('ticket_id', $request->ticket_id)->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'material_rec' => $ticket->material_rec,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function updateDeatils(Request $request)
{
    Log::info('📥 Incoming JSON:', $request->all());

    // Validate input
    $request->validate([
        'ticket_id' => 'required|string',
        'comment' => 'required|string',
    ]);

    // Check if ticket exists using raw SQL
    $ticket = DB::select('SELECT * FROM tickets WHERE ticket_id = ? LIMIT 1', [$request->ticket_id]);

    if (empty($ticket)) {
        Log::error('❌ No ticket found with ID:', ['ticket_id' => $request->ticket_id]);

        return response()->json([
            'success' => false,
            'message' => 'No ticket found with the given ID.',
        ], 404);
    }

    // Update using raw SQL
    DB::update('UPDATE tickets SET material_rec = ? WHERE ticket_id = ?', [
        $request->comment,
        $request->ticket_id,
    ]);

    Log::info('✅ Material comment updated for ticket_id: ' . $request->ticket_id);

    return response()->json([
        'success' => true,
        'message' => 'Material updated successfully.',
    ]);
}



}
?>
