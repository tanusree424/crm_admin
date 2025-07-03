<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket\Category;
use App\Models\Projects_category;

class Location extends Model
{
    use HasFactory;

    protected $table = 'location';

        protected $fillable = [
        'country_id',
        'location',
        // add other fields you want to mass assign
    ];

}
