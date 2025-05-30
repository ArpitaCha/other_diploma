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
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => ['The Excel file is empty.'],
            ]);
        }

        $firstRow = $rows->first();
      

        $actualHeaders = array_keys($firstRow->toArray());
       
        $missingHeaders = array_diff($this->expectedHeaders, $actualHeaders);

        if (!empty($missingHeaders)) {
            throw ValidationException::withMessages([
                'file' => ['Missing or incorrect headers: ' . implode(', ', $missingHeaders)],
            ]);
        }
        
        foreach ($rows as $row) {
            UserTwo::create([
                'id'   => $row['id'],
                'username' => $row['username'],
                'phone_no' => $row['phone_no'],
                'u_role_id'   => $row['u_role_id'],
                'u_inst_id' => $row['u_inst_id'],
                'email' => $row['email'],
            ]);
        }
        
    }
}
