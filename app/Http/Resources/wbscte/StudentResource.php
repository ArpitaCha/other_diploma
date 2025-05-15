<?php

namespace App\Http\Resources\wbscte;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'student_reg_no'                    =>  $this->student_reg_no,
            'student_reg_year'                  =>  $this->student_reg_year,
            'student_session_yr'                  =>  $this->student_session_yr,
            'student_course_id'    =>  $this->course->course_id_pk,
            'student_course_name'    =>  $this->course->course_name,
            'student_course_code'    =>  $this->course->course_code,
            'student_inst_id'    =>  $this->institute->inst_sl_pk,
            'student_inst_name'    =>  $this->institute->institute_name,
            'student_fname'                      =>  $this->student_fname,
            'student_mname'                      =>  $this->student_mname,
            'student_lname'                      =>  $this->student_lname,
            'student_father_name'               =>  $this->student_father_name, 
            'student_semester'                =>  $this->student_semester,
            'student_dob' => $this->student_dob,
            'student_aadhar_no' => $this->student_aadhar_no,
            'student_mobile_no' => $this->student_mobile_no,
            'student_email' => $this->student_email,
            'student_address' => $this->student_address, 
            'student_institute_category' =>$this->student_institute_category ,
            'student_application_for'=>$this->student_application_for ,
            'student_is_enrolled' => $this->student_is_enrolled, 
            'student_exam_fees_status' =>$this->student_exam_fees_status ,
            'student_approve_reject_status'=>$this->student_approve_reject_status ,
            'student_application_form_num'=>$this->student_form_num,
            'student_district'=>$this->district->district_id_pk,
            'student_state'=>$this->state->state_id_pk,
            'student_gender'=>$this->student_gender,
            'student_pwd'=>$this->student_pwd== '1' ? 'Yes' : 'No',
            'student_marital'=>$this->student_marital== '1' ? 'Married' : 'Unmarried',
            'student_subdivision'=>$this->subdivision->id,
            'student_status_code'=>$this->student_status_s1,
            'student_status'=>$this->getStudentStatus(),
            'student_caste'=>$this->student_caste,
            'student_religion'=>$this->student_religion,
            'student_citizenship'=>$this->student_citizenship,
            'student_kanyashree_no'=>$this->student_kanyashree_no,
            'student_pin_code'=>$this->student_pin_code,
            'student_profile_pic'=>$this->student_profile_pic ? URL::to("storage/{$this->student_profile_pic}"):null,
            'student_signature'=>$this->student_signature ? URL::to("storage/{$this->student_signature}"):null,
            'is_applied' => $this->student_status_s1 >= 1,//applicatiojn
            'is_paid' => $this->student_status_s1 >= 2,//student appl fees
            'is_verified' => $this->student_status_s1 >= 3,//inst first
            // 'is_updated' => $this->student_status_s1 >= 4,//profil_pic
            'is_reg_fees' => $this->student_status_s1 >= 5,//student fees reg
            'is_approved' => $this->student_status_s1 >= 6,//council
            'is_rejected' => $this->student_status_s1 == 9,//rejected
           'student_name'=>$this->student_fullname,
           'student_guardian_name'=>$this->student_guardian_name,
           'is_approved_all'=> $this->student_status_s1 >= 6,
           
           
            



        ];
    }
    private function getStudentStatus()
    {
        $statuses = [
            '1' => 'Application Done',
            '2' => 'Fees Paid',
            '3' =>'Profile Not Updated',
            '4' => 'Profile Updated',
            '5' => 'Registration Fees Paid',
            '6' => 'Approved',
            '9' => 'Rejected'
        ];

        return $statuses[$this->student_status_s1] ;
    }
}
