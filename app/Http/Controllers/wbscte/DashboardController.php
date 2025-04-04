<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\wbscte\Student;
use App\Http\Resources\wbscte\StudentResource;

class DashboardController extends Controller
{
    public function studentDetail(Request $request, $student_id)
    {
        $student = Student::where('student_id_pk', $student_id)->with('course:course_id_pk,course_name,course_code','institute:inst_sl_pk,institute_name','state:state_id_pk,state_name','district:district_id_pk,district_name','subdivision:id,name')->first();
        // dd($student);
        
        if ($student) {
            $reponse = array(
                'error'                 =>  false,
                'message'               =>  'Data Found',
                // 'count'                 =>   sizeof($dashboardNotificationList),
                'student' =>   new StudentResource($student)
            );
            return response(json_encode($reponse), 200);
        } else {
            $reponse = array(
                'error'     =>  true,
                'message'   =>  'No Student available'
            );
            return response(json_encode($reponse), 200);
        }
        
    }
    
}
