<?php

namespace App\Imports;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Hash;
use Throwable;
use App\Models\Inventory;


class InventoryImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use Importable, SkipsErrors;

 public function model(array $row)
    {
        return new Inventory([
            'quantity' => $row['quantity'] ?? null,
            'created_at' => now(),
            'created_by' => auth()->id(),
            'material_id' => $row['spare_id'] ?? null,
            'country_id' => $row['country_id'] ?? null,
            'location_id' => $row['location_id'] ?? null,
            'spare_code' => $row['spare_code'] ?? null,
            'spare_name' => $row['spare_name'] ?? null,
            'spare_description' => $row['spare_description'] ?? null,
        ]);
    }

        public function rules(): array
        {
            return [
                'Quantity' => 'required|integer|min:1',
                'Spare ID' => 'required|exists:materials,id',
                'Country ID' => 'required|exists:countries,id',
                'Location ID' => 'required|exists:location,id',
                'Spare Code' => 'required|string|min:1|max:10000', // must not be empty
                'Spare Name' => 'required|string|min:1|max:10000', // must not be empty
                'Spare Description' => 'nullable|string|max:10000',
            ];
        }
}
