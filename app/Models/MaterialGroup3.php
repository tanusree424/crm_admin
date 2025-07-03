<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialGroup3 extends Model
{
    use HasFactory;

    protected $table = 'materialgroup3';

    protected $fillable = [
        'name',
        'description'
    ];

    
}
