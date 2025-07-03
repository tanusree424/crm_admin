<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket\Category;
use App\Models\Projects_category;

class MaterialGroup2 extends Model
{
    use HasFactory;

    protected $table = 'materialgroup2';

    protected $fillable = [

        'name',
        'description'
    ];

    
}
