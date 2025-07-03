<?php

namespace App\Imports;

use App\Models\Material;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MaterialsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Material([
            'material_code' => $row['material_code'],
            'material_name' => $row['material_name'],
            'description'   => $row['description'],
            'material_group_code1'   => $row['material_group_code1'],
            'material_group_code2'   => $row['groupmaterial__code2'],
            'material_group_code3'   => $row['groupmaterial__code3'],
            'mrp'           => $row['mrp'],
            'division_code' => $row['division_code'],
            'isserialized'  => $row['isserialized'],
            'isrepairable'  => $row['isrepairable'],
            'isonsiteallowed' => $row['isonsiteallowed'],
            'is_active' => $row['is_active'], // New columns
            'warranty_years' => $row['warranty_years'],
            'warrant_days' => $row['warrant_days'],
            'numberofrepair' => $row['numberofrepair'],
            'is_servicecharge_applicable' => $row['is_servicecharge_applicable'],
        ]);
    }
}
