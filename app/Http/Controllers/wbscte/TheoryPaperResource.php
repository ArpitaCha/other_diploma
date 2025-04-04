<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TheoryPaperResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            "paper_id" => $this->paper_id_pk,
            "institute_name" => $this->institute->institute_name,
            "inst_id" => $this->inst_id,
            "course_id" => $this->course_id,
            "paper_code" => $this->paper_code,
            "paper_name" => $this->paper_name,
            "paper_type" => $this->paper_type,
            "paper_category" =>(int)$this->paper_category,
            "paper_credit" => $this->paper_credit,
            "paper_full_marks" => $this->paper_full_marks,
            "paper_internal_marks" => $this->paper_internal_marks,
            "paper_external_marks" => $this->paper_external_marks,
            "paper_pass_marks" => $this->paper_pass_marks,
            "paper_semester" =>  $this->paper_semester,
            "paper_sessional_internal_viva_marks" =>  ($this->paper_category == '2')?$this->paper_sessional_internal_viva_marks:'',
           "paper_sessional_internal_class_test_marks" =>  ($this->paper_category == '2')?$this-> paper_sessional_internal_class_test_marks:'',
           "paper_sessional_internal_attendance_marks" =>  ($this->paper_category == '2')?$this-> paper_sessional_internal_attendance_marks:'',
           "paper_sess_assign_viva_marks" =>  ($this->paper_category == '2')?$this-> paper_sess_assign_viva_marks:'',
           "paper_sess_before_viva_marks" =>  ($this->paper_category == '2')?$this-> paper_sess_before_viva_marks:'',
           "paper_internal_attendance_marks" =>  ($this->paper_category == '1')?$this-> paper_internal_attendance_marks:'',
            "paper_internal_theory_class_test_marks" =>  ($this->paper_category == '1')?$this->paper_internal_theory_class_test_marks:'',
           "paper_internal_theory_viva_marks" =>  ($this->paper_category == '1')?$this-> paper_internal_theory_viva_marks:'',
            "is_active" =>  $this->is_active,
            "paper_affiliation_year" => $this->paper_affiliation_year,


            // 'paper_attendance_marks'=>$this->paper_internal_attendance_marks
        ];
    }
}
