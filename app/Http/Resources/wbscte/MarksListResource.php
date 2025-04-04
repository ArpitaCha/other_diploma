<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarksListResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        $max_marks = $this->maxMarks($request->paper_type,$request->entry_type);
        //  dd($max_marks);
        //dd((bool)$this->marks->is_final_submit);

        return [
             "attendence_id" => $this->att_id,
            "registration_no" => $this->att_reg_no,
            "student_name" => optional($this->student)->student_fullname,
            "is_present" => (bool)$this->att_is_present,
            "is_absent" => (bool)$this->att_is_absent,
            "is_ra" => (bool)$this->att_is_ra,
            'is_final' => !is_null($this->marks->is_final_submit) ? (bool)$this->marks->is_final_submit : false,
            'paper_marks' => [
                'marks_id' => optional($this->marks)->id,
                "internal_marks" => $request->entry_type == 1 ? optional($this->marks)->marks ?
                (!is_null($this->marks->marks) ? $this->marks->marks
                    : ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null))) : ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null)) : null,
                    "marks" => $request->entry_type == 2 ? optional($this->marks)->marks ?
                        (!is_null($this->marks->marks) ? $this->marks->marks
                            : ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null))) : ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null)):null,
                "attendance_marks" => optional($this->marks)->internal_attendance_marks ?
                    (!is_null($this->marks->internal_attendance_marks) ? $this->marks->internal_attendance_marks
                        : ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null))) : ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null)),
                
                   
                     "theory_test_marks"=> optional($this->marks)->internal_class_test_marks ? (!is_null($this->marks->internal_class_test_marks) ? $this->marks->internal_class_test_marks: ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null))) : ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null)),
                    "theory_viva_marks" =>  optional($this->marks)->internal_viva_marks ? (!is_null($this->marks->internal_viva_marks) ? $this->marks->internal_viva_marks: ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null))) : ($this->att_is_absent  ? 'AB' : ($this->att_is_ra  ? 'RA' : null)),       
            ],
            'max_marks' => $max_marks
        ];
    }

    private function maxMarks($paper_type,$subject_entry_type)
    {
        
        if ($paper_type == 1  && $subject_entry_type == 1) {//internal theory
            return [
                'internal_marks' => (int)optional($this->paperMarks)->paper_internal_marks,
                // 'full_marks' =>(int)optional($this->paperMarks)->paper_full_marks,
                'internal_attendance_marks' => (int)optional($this->paperMarks)->paper_internal_attendance_marks,
                'external_marks' => (int)optional($this->paperMarks)->paper_external_marks,
                'theory_test_marks'=>(int)optional($this->paperMarks)->paper_internal_theory_class_test_marks ,
                'theory_viva_marks' => (int)optional($this->paperMarks)->paper_internal_theory_viva_marks ,
            ];
        } elseif ($paper_type == 2 && $subject_entry_type == 1) { //internal sessional
           return [
                  'internal_marks' => (int)optional($this->paperMarks)->paper_internal_marks,
                  'internal_attendance_marks' => (int)optional($this->paperMarks)->paper_sessional_internal_attendance_marks,
                //  'full_marks' =>(int)optional($this->paperMarks)->paper_full_marks,
                  'external_marks' => (int)optional($this->paperMarks)->paper_external_marks,
                  'theory_test_marks'=>(int)optional($this->paperMarks)->paper_sessional_internal_class_test_marks ,
                 'theory_viva_marks' => (int)optional($this->paperMarks)->paper_sessional_internal_viva_marks,
            ];
        }elseif($paper_type == 2 && $subject_entry_type == 2){//external sessional
            return[
                'theory_viva_marks' =>(int)optional($this->paperMarks)->paper_sess_assign_viva_marks,//before viva marks
                'theory_test_marks'=>(int)optional($this->paperMarks)->paper_sess_before_viva_marks,
                'external_marks' => (int)optional($this->paperMarks)->paper_external_marks//assign marks
             
            ];

        }elseif($paper_type == 1 && $subject_entry_type == 2){//theory external
            return [
                'external_marks' =>  (int)optional($this->paperMarks)->paper_external_marks
             
            ];
        

        }
    }
}
