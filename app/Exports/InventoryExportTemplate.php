<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Inventory;

class InventoryExportTemplate implements FromCollection, WithHeadings
{
    protected $custom;

    public function __construct($custom = false)
    {
        $this->custom = $custom;
    }

    public function collection()
    {
        return $this->custom 
            ? $this->exportCustom() 
            : $this->defaultExport();
    }

    public function headings(): array
    {
        return [
            'Country ID',
            'Spare ID',
            'Location ID',
            'Quantity',
            'Spare Code',
            'Spare Name',
            'Spare Description',
            'Country Name',
            'Location'
        ];
    }

public function defaultExport()
{
    return DB::table('materials as m')
        ->leftJoin('inventory as i', 'm.id', '=', 'i.material_id')
        ->leftJoin('countries as c', 'c.id', '=', 'i.country_id')
        ->leftJoin('location as l', 'l.id', '=', 'i.location_id') // ✅ fix here
        ->select(
            'c.id as country_id',
            'm.id as spare_id',
            'i.location_id',
            'i.quantity',
            'm.material_code as spare_code',
            'm.material_name as spare_name',
            'm.material_description as spare_description',
            'c.name as country_name',
            'l.location'
        )
        ->get()
        ->map(function ($item) {
            return [
                'country_id' => $item->country_id,
                'spare_id' => $item->spare_id,
                'location_id' => $item->location_id,
                'Quantity' => $item->quantity,
                'Spare Code' => $item->spare_code,
                'Spare Name' => $item->spare_name,
                'Spare Description' => $item->spare_description,
                'Country Name' => $item->country_name,
                'Location' => $item->location
            ];
        });
}

    public function exportCustom()
    {
        $sql = "
            SELECT c.id as country_id, m.id as material_id as spare_id, i.location_id,
                i.quantity AS quantity,
                m.material_code AS spare_code,
                m.material_name AS spare_name,
                m.material_description AS spare_description,
                c.name AS country_name,
                l.location AS location
            FROM materials m 
            LEFT JOIN inventory i ON m.id = i.material_id
            LEFT JOIN countries c ON c.id = i.country_id
            LEFT JOIN location l ON l.id = i.location_id";

        $results = DB::select($sql);

        return collect($results)->map(function ($item) {
            return [
                'country_id' => $item->country_id,
                'Spare ID' => $item->spare_id,
                'location_id' => $item->location_id,
                'Quantity' => $item->quantity,
                'Spare Code' => $item->spare_code,
                'Spare Name' => $item->spare_name,
                'Spare Description' => $item->spare_description,
                'Country Name' => $item->country_name,
                'Location' => $item->location
            ];
        });
    }
}