<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket\Category;
use App\Models\Projects_category;
use Illuminate\Support\Facades\DB;

class StockTransfer extends Model
{

    use HasFactory;

    protected $table = 'stock_transfer';
        protected $fillable = [
        'material_id',
        'inventory_id',
        'source_location_id',
        'destination_location_id',
        'quantity',
        'transfer_status',
        'transfer_date',
        'created_at',
        'created_by',
    ];


public static function findAll()
{
    // $rowSql = "
    //     SELECT  
    //         s.id, 
    //         s.ticket_id, 
    //         s.brand, 
    //         s.product_type, 
    //         s.material, 
    //         CAST(i.quantity AS DECIMAL(10,2)) AS inventory_qty, 
    //         CAST(s.quantity AS DECIMAL(10,2)) AS transfer_qty, 
    //         (CAST(i.quantity AS DECIMAL(10,2)) - CAST(s.quantity AS DECIMAL(10,2))) AS difference_qty, 
    //         s.invoice_no, 
    //         s.invoice_date,
    //         s.replacement_applicable, 
    //         s.replacement_reason_type, 
    //         s.replacement_reason, 
    //         s.pickup_needed, 
    //         s.warranty_status, 
    //         s.created_at, 
    //         s.updated_at, 
    //         s.transfer_date, 
    //         s.awb_no
    //     FROM stock_transfer s 
    //     JOIN inventory i 
    //         ON TRIM(s.material) = TRIM(CONCAT(i.spare_code, ' - ', i.spare_name))
    // ";
    
    
        $rowSql = "
        WITH ranked_transfers AS (
    SELECT  
        s.id, 
        s.ticket_id, 
        s.brand, 
        s.product_type, 
        s.material, 
        CAST(i.quantity AS DECIMAL(10,2)) AS inventory_qty, 
        CAST(s.quantity AS DECIMAL(10,2)) AS transfer_qty, 
        (CAST(i.quantity AS DECIMAL(10,2)) - CAST(s.quantity AS DECIMAL(10,2))) AS difference_qty, 
        s.invoice_no, 
        s.invoice_date,
        s.replacement_applicable, 
        s.replacement_reason_type, 
        s.replacement_reason, 
        s.pickup_needed, 
        s.warranty_status, 
        s.created_at, 
        s.updated_at, 
        s.transfer_date, 
        s.awb_no,
        ROW_NUMBER() OVER (PARTITION BY s.ticket_id, s.material ORDER BY s.created_at DESC) AS rn
    FROM stock_transfer s 
    JOIN inventory i 
        ON TRIM(s.material) = TRIM(CONCAT(i.spare_code, ' - ', i.spare_name))
)
SELECT *
FROM ranked_transfers
WHERE rn = 1;

    ";

    return DB::select($rowSql);
}

}

