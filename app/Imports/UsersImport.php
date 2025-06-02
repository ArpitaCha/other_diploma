<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\wbscte\User; 
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
        'u_id','u_inst_id', 'u_username','u_fullname', 'u_phone','u_email','bank_account_holder_name','bank_account_no','bank_ifsc','bank_branch_name','u_role_id', 'is_active', 'created_at','updated_at','is_direct','assign_status'
    ];

    public function collection(Collection $rows)
    {
        $expectedHeaders = [
       'u_id','u_inst_id', 'u_username','u_fullname', 'u_phone','u_email','bank_account_holder_name','bank_account_no','bank_ifsc','bank_branch_name','u_role_id', 'is_active', 'created_at','updated_at','is_direct','assign_status'
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
            User::create([
                'u_username' => $row['u_username'],
                'u_fullname' => $row['u_fullname'],
                'bank_account_holder_name' => $row['bank_account_holder_name'],
                'bank_account_no'=> $row['bank_account_no'],
                'bank_ifsc'=> $row['bank_ifsc'],
                'bank_branch_name'=> $row['bank_branch_name'],
                'is_active'=> $row['is_active'],
                'created_at'=> $row['created_at'],
                'updated_at'=> $row['updated_at'],
                'is_direct'=> $row['is_direct'],
                'assign_status'=> $row['assign_status'],
                'u_phone' => $row['u_phone'],
                'u_role_id'   => $row['u_role_id'],
                'u_inst_id' => $row['u_inst_id'],
                'u_email' => $row['u_email'],
            ]);
        }
        
    }
}
