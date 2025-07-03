<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket\Category;
use App\Models\Projects_category;

class MaterialGroup1 extends Model
{
    use HasFactory;

    protected $table = 'materialgroup1';

    protected $fillable = [
        'name',
        'description'
    ];

    
}
