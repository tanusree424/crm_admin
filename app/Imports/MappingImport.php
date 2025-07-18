<?php
namespace App\Imports;

use App\Models\Mapping;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Customer;
use App\Models\Department;

class MappingImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;




public function model(array $row)
{
    \Log::info('Row Data:', $row);

    try {
        // Optionally find user by empid
        $user = isset($row['empid']) ? User::find($row['empid']) : null;

        // Log the user result
        \Log::info('Resolved User:', ['user' => $user]);

        $mapping = new Mapping([
            'empid'         => $row['empid'] ?? null,
            'emp_code'      => $row['emp_code'] ?? null,
            'customer_name' => $row['customer_name'] ?? null,
            'modules'       => $row['modules'] ?? null,
        ]);

        $mapping->save();

        \Log::info('Mapping saved:', ['id' => $mapping->id]);

        return $mapping;

    } catch (\Exception $e) {
        \Log::error('Error saving mapping:', [
            'row' => $row,
            'message' => $e->getMessage()
        ]);
    }
}


public function rules(): array
    {
        return [
            'emp_code'       => 'required|string',
            'customer_name'  => 'required|string',
            'modules'        => 'required|string',
        ];
    }

}
