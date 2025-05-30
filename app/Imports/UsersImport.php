<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\UserTwo; 
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Validation\ValidationException;

class UsersImport implements ToCollection, WithHeadingRow
{
    /**
    * @ @param \Illuminate\Support\Collection $rows
    */
     protected $expectedHeaders = [
        'id', 'username', 'phone_no', 'u_role_id', 'u_inst_id', 'email'
    ];

    public function collection(Collection $rows)
    {
        $expectedHeaders = [
        'id', 'username', 'phone_no', 'u_role_id', 'u_inst_id', 'email','ad'
    ];
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => ['The Excel file is empty.'],
            ]);
        }

        $firstRow = $rows->first();
        
        $actualHeaders = array_map(
            fn($header) => trim(strtolower($header)),
            array_keys($firstRow->toArray())
         );
        $expectedHeaders = array_map('strtolower', $expectedHeaders);
        $missingHeaders = array_diff($expectedHeaders, $actualHeaders);
       

        if (!empty($missingHeaders)) {
            throw ValidationException::withMessages([
                'file' => ['Missing or incorrect headers: ' . implode(', ', $missingHeaders)],
            ]);
        }
        foreach ($rows as $row) {
            UserTwo::create([
                'username' => $row['username'],
                'phone_no' => $row['phone_no'],
                'u_role_id'   => $row['u_role_id'],
                'u_inst_id' => $row['u_inst_id'],
                'email' => $row['email'],
            ]);
        }
        
    }
}
