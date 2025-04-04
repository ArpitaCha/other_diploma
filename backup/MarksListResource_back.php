<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarksListResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        $max_marks = $this->maxMarks($request->paper_type);

        return [
            "attendence_id" => $this->att_id,
            "registration_no" => $this->att_reg_no,
            "student_name" => optional($this->student)->student_fullname,
            "is_present" => (bool)$this->att_is_present,
            'is_final' => (bool)$this->is_final_submit,
            'paper_marks' => [
                'marks_id' => optional($this->marks)->id,
                "marks" => optional($this->marks)->marks ? (int)$this->marks->marks : ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null)),
            ],
            'max_marks' => $max_marks
        ];
    }

    private function maxMarks($paper_type)
    {
        if ($paper_type == 1) {
            return [
                'internel_marks' => (int)optional($this->paperMarks)->paper_internal_marks,
                'enternel_marks' => (int)optional($this->paperMarks)->paper_external_marks,
                'attendance_marks' => (int)optional($this->paperMarks)->paper_internal_attendance_marks,
            ];
        }

        if ($paper_type == 2) {
            return [
                'internel_marks' => (int)optional($this->paperMarks)->paper_sessional_theory_marks,
                'enternel_marks' => (int)optional($this->paperMarks)->paper_sessional_practical_marks
            ];
        }
    }
}
