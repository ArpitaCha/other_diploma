<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExaminerInternalResource extends JsonResource
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
            'examiner_user_id'                    =>  $this->user->u_id,
            'examiner_name'                    =>  $this->user->u_fullname,
            'institute_code'                  =>  $this->examiner_inst_code,
            'institute_name'                  =>  $this->institute->institute_name,
            'course_name'    =>  optional($this->course)->course_name ?? 'N/A',
            'course_code'    =>  optional($this->course)->course_code ?? 'N/A',
            'course_type'    =>  optional($this->course)->course_type ?? 'N/A',
            'course_duration'    =>  optional($this->course)->course_duration ?? 'N/A',
            'paper_name'    =>  optional($this->paper)->paper_name ?? 'N/A',
            'paper_code'    =>   optional($this->paper)->paper_code ?? 'N/A',
            'status' => $this->is_active == '1' ? 'Active' : 'Inactive',
            'phone' =>  optional($this->user)->u_phone ?? 'N/A',
            'email' =>  optional($this->user)->u_email ?? 'N/A',
            'map_paper_type'=>$this->map_paper_type == '1' ? 'Theory' : 'Sessional', 
            'map_paper_entry_type'=>$this->map_paper_entry_type == '1' ? 'Internal' : 'External', 
            'session_year_course'=> optional($this->course)->course_affiliation_year ?? 'N/A',
        ];
    }
}
