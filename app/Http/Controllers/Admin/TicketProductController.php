<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketProductController extends Controller
{
    /**
     * Store or update ticket product details using raw SQL.
     */
    public function save(Request $request)
    {
        try {
            // ✅ Validate the request
            $validated = $request->validate([
                'ticket_id'   => 'required',
                'awb_number'  => 'nullable|string|max:255',
                'date'        => 'required|date',
                'mat_rec'     => 'required|in:yes,no',
                'mat_reason'  => 'required|string|max:255',
            ]);

            // ✅ Raw SQL: Insert or update if ticket_id already exists
            DB::insert('
                INSERT INTO `ticketproducts` (`ticket_id`, `AWB_number`, `date`, `material_rec`, `mat_reason`, `created_at`, `updated_at`)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    `AWB_number` = VALUES(`AWB_number`),
                    `date` = VALUES(`date`),
                    `material_rec` = VALUES(`material_rec`),
                    `mat_reason` = VALUES(`mat_reason`),
                    `updated_at` = NOW()
            ', [
                $validated['ticket_id'],
                $validated['awb_number'],
                $validated['date'],
                $validated['mat_rec'],
                $validated['mat_reason']
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Log::error('Insert or Update Failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
