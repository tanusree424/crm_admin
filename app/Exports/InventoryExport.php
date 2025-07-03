<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Inventory;

class InventoryExport implements FromCollection, WithHeadings
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
            'Quantity',
            'Created At',
            'Created By',
            'Spare Code',
            'Spare Name',
            'Spare Description',
            'Country Name',
            'Location'
        ];
    }

    public function defaultExport()
    {
        return Inventory::with(['country', 'location'])
            ->join('materials as m', 'm.id', '=', 'inventory.material_id')
            ->join('countries as c', 'c.id', '=', 'inventory.country_id')
            ->join('location as l', 'l.id', '=', 'inventory.location_id')
           ->join('users as u', 'u.id', '=', 'inventory.created_by')
            ->select(
                'inventory.quantity',
                'inventory.created_at',
                'm.material_code as spare_code',
                'm.material_name as spare_name',
                'm.material_description as spare_description',
                'c.name as country_name',
                'l.location',
                'u.firstname as created_by'
            )
            ->get()
            ->map(function ($item) {
                return [
                    'Quantity' => $item->quantity,
                    'Created At' =>  Carbon::parse($item->created_at)->format('d-m-Y'),
                    'Created By' => $item->created_by,
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
            SELECT 
                i.quantity AS quantity,
                i.created_at AS created_at,
                m.material_code AS spare_code,
                m.material_name AS spare_name,
                m.material_description AS spare_description,
                c.name AS country_name,
                l.location AS location,
                u.firstname as created_by
            FROM inventory i
            JOIN materials m ON m.id = i.material_id
            JOIN countries c ON c.id = i.country_id
            JOIN location l ON l.id = i.location_id
            JOIN users u on u.id = i.created_by";

        $results = DB::select($sql);

        return collect($results)->map(function ($item) {
            return [
                'Quantity' => $item->quantity,
                'Created At' =>  Carbon::parse($item->created_at)->format('d-m-Y'),
                'Created By' => $item->created_by,
                'Spare Code' => $item->spare_code,
                'Spare Name' => $item->spare_name,
                'Spare Description' => $item->spare_description,
                'Country Name' => $item->country_name,
                'Location' => $item->location
            ];
        });
    }
}

