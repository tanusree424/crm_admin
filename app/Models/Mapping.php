<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Department;
use App\Models\Customer;

class Mapping extends Model
{
    use HasFactory;

    protected $table = 'usersmapping';

    protected $fillable = [
        'empid',
        'modules',
        'customer',
        'customer_name',
        'emp_code',
        'empid',
        'status',
        'created_at',
        'updated_at'
    ];

    // Relationship with Department
  public function user()
{
    return $this->belongsTo(User::class, 'empid', 'id'); // 'empid' is your FK in mappings table
}

public function customer()
{
    return $this->belongsTo(Customer::class, 'customer', 'id'); // assuming 'customer_id' is FK
}




}
