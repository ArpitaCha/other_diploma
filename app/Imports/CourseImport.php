<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\wbscte\Course; 
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Validation\ValidationException;

class CourseImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
     

    public function collection(Collection $collection)
    {
        $expectedHeaders = [
       'course_name','course_code', 'inst_id','course_duration', 'is_active','course_affiliation_year','course_type','course_id_pk','exam_year'];
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
            Course::create([
                'course_name' => $row['course_name'],
                'course_code' => $row['course_code'],
                'inst_id' => $row['inst_id'],
                'course_duration'=> $row['course_duration'],
                'is_active'=> $row['is_active'],
                'course_affiliation_year'=> $row['course_affiliation_year'],
                'course_type'=> $row['course_type'],
                'course_id_pk'=> $row['course_id_pk'],
                'exam_year'=> $row['exam_year'],
              
            ]);
        }
        
    }

        
    
}
