<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'student_id' => $this->att_id,
            'student_reg_no' => $this->att_reg_no,
            'student_name' => optional($this->student)->student_fullname,
            'is_present' => $this->att_is_present,
            'is_absent' => $this->att_is_absent,
            'is_ra' => $this->att_is_ra,
            'is_final' => $this->is_final_submit
        ];
    }
}
