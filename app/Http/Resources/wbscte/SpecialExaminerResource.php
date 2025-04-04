<?php

namespace App\Http\Resources\wbscte;

use App\Models\wbscte\Course;
use Illuminate\Http\Request;
use App\Models\wbscte\Institute;
use App\Models\wbscte\Paper;
use Illuminate\Http\Resources\Json\JsonResource;

class SpecialExaminerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        $inst = Institute::select('institute_name')->find($this->map_assign_inst_id);
        $course = Course::select('course_name')->find($this->map_course_id);
        $paper = Paper::select('paper_name')->find($this->map_paper_id);

        return [
            'examiner_id' => $this->map_id,
            'examiner_name' => $this->map_examiner_name,
            'institute_name' => $inst->institute_name,
            'course_name' => $course->course_name,
            'paper_name' => $paper->paper_name,
            'paper_type' => $paper->map_paper_type == 1 ? 'Theory' : 'Sessional',
            'entry_type' => $paper->map_paper_entry_type == 1 ? 'Internal' : 'External',
        ];
    }
}
