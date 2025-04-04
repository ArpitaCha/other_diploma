<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InternalExaminerShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'examiner_id'                    =>  $this->examiner_id,
            'examiner_name'                    =>  $this->user->u_fullname,
            'institute_code'                  =>  $this->examiner_inst_code,
            'examiner_inst_id'                  =>  $this->examiner_inst_id,
            'examiner_paper_id'                  =>  $this->examiner_paper_id,
            'examiner_course_id'                  =>  $this->examiner_course_id,
            'examiner_phone'                  =>  $this->user->u_phone,
            'examiner_email'                  =>  $this->user->u_email,
            'examiner_part_sem'                  =>  $this->examiner_part_sem,
            'examiner_bank_account_holder_name'  =>  $this->user->bank_account_holder_name,
            'examiner_bank_account_no'                  =>  $this->user->bank_account_no,
            'examiner_bank_ifsc'                  =>  $this->user->bank_ifsc,
            'examiner_bank_branch_name'                  =>  $this->user->bank_branch_name,
            'is_active'                         => $this->is_active,
            'map_paper_entry_type'              => $this->map_paper_entry_type,
            'map_paper_type'              => $this->map_paper_type,
            'session_year_course'              => $this->course->course_affiliation_year,
        ];
    }
}
