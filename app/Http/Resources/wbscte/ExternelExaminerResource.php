<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExternelExaminerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        return [
            'ext_examiner_id' => $this->map_id,
            'ext_examiner_name' => $this->map_examiner_name,
            'institute_from' => optional($this->internelInstituteName)->institute_name ?? 'N/A',
            'institute_to' => optional($this->ExternerInstituteName)->institute_name,
            'course_name'    =>  optional($this->course)->course_name ?? 'N/A',
            'map_paper_type'=>$this->map_paper_type == '1' ? 'Theory' : 'Sessional',
            'map_paper_entry_type'=>$this->map_paper_entry_type == '1' ? 'Internal' : 'External',
            'paper'    =>  optional($this->paper)->paper_name ?? 'N/A',
            'phone' => optional($this->user)->u_phone ?? 'N/A',
            'session_year'=> $this->map_affiliation_year,
            'semester'=> $this->map_sem,
            // 'phone_no' => $this->map_examiner_name,
        ];
    }
}
