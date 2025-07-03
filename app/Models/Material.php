<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket\Category;
use App\Models\Projects_category;

class Material extends Model
{
    use HasFactory;

    protected $table = 'materials';

    protected $fillable = [
        'material_code',
        'material_name',
        'material_description',
        'material_group_code1',
        'material_group_code2',
        'material_group_code3',
        'mrp',
        'division_code',
        'isserialized',
        'isrepairable',
        'isonsiteallowed',
        'is_active', // New columns
        'warranty_years',
        'warrant_days',
        'numberofrepair',
        'is_servicecharge_applicable',
    ];


    protected $casts = [
        'isserialized' => 'boolean',
        'isrepairable' => 'boolean',
        'isonsiteallowed' => 'boolean',
        'is_active' => 'boolean', 
        'is_servicecharge_applicable' => 'boolean',
    ];


    public function group1()
    {
        return $this->belongsTo(MaterialGroup1::class, 'material_group_code1');
    }

    public function group2()
    {
        return $this->belongsTo(MaterialGroup2::class, 'material_group_code2');
    }

    public function group3()
    {
        return $this->belongsTo(MaterialGroup3::class, 'material_group_code3');
    }
}
