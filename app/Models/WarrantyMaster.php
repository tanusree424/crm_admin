<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_code',
        'country_code',
        'country_name',
        'mat_group2_code',
        'mat_group2_desc',
        'mat_group3_code',
        'mat_group3_desc',
        'doa_applicable',
        'doa_days',
        'swap_applicable',
        'swap_days',
        'ow_swap_applicable',
        'warranty_class',
        'warranty_desc',
        'defer_warranty_code',
        'defer_desc',
        'policy_applicable',
        'policy_type',
        'policy_desc',
        'insurance_code',
        'insurance_desc',
        'insurance_days',
        'insurance_count',
        'valid_from',
        'valid_to',
        'sales_mode',
        'number_of_repair',
        'is_servicecharge_applicable',
    ];
}
