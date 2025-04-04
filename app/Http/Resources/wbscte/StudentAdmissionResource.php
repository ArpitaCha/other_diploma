<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAdmissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            
            'student_course_name'    =>  $this->course->course_name,
            'student_course_code'    =>  $this->course->course_code,
            'student_inst_id'    =>  $this->institute->inst_sl_pk,
            'student_inst_name'    =>  $this->institute->institute_name,
            'student_name'                      =>  $this->student_fullname,
            'student_semester'                =>  $this->student_semester,
            'student_mobile_no' => $this->student_mobile_no,
            'student_email' => $this->student_email,
            'student_application_form_num'=>$this->student_form_num,
            'student_gender'=>$this->student_gender,
            'student_status'=>$this->student_status_s1,
        ];
    }
}
