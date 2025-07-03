<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Ticket\Category;
use App\Models\Projects_category;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';
        protected $fillable = [
        'country_id',
        'location_id',
        
        'spare_code',
        'spare_name',
        'spare_description',
        'quantity',
        'material_id',
        'created_by',
        // add other fields you want to mass assign
    ];


    
    public function country()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }

    public function location()
    {
        return $this->belongsTo(Timezone::class, 'location_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function projectCategory()
    {
        return $this->belongsTo(Projects_category::class, 'project_category_id');
    }
    

// row sql code for all data

public static  function allMaterials(){
    $rowSql ='SELECT i.id, l.location, c.name AS country_name,
     m.material_code, m.material_name, i.quantity, DATE_FORMAT(i.created_at, "%d-%m-%Y") as created_at FROM inventory i 
     JOIN materials m ON m.id = i.material_id
     JOIN countries c ON c.id = i.country_id
     JOIN location l ON l.id = location_id';
    return DB::select($rowSql);
}


public static function GetInventryList(){
    $rowSql = 'SELECT m.id AS material_id, i.id AS inventry_id, m.material_code, i.location_id, m.material_name, i.quantity, i.spare_description FROM inventory i JOIN materials m ON m.id = i.material_id';
    return DB::select($rowSql);
}


}

