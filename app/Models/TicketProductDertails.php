<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketProductDertails extends Model
{
    use HasFactory;

    protected $table="ticketproducts";

    protected $fillable = [
        'ticket_id',
        'brand',
        'product_type',
        'material',
        'quantity',
        'invoice_no',
        'invoice_date',
        'replacement_applicable',
        'replacement_reason_type',
        'replacement_reason',
        'pickup_needed',
        'warranty_status',
        'AWB_number',
        'material_rec',
        'mat_reason',
        'date'
    ];
}
