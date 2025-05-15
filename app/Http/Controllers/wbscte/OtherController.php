<?php

namespace App\Http\Controllers\wbscte;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\PaymentLib\AESEncDec;
use App\Models\wbscte\Paper;
use App\Models\wbscte\Institute;
use App\Models\wbscte\Course;
use App\Models\wbscte\Attendance;
use App\Models\wbscte\Result;
use App\Models\wbscte\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\wbscte\PaymentTransaction;

class OtherController extends Controller
{
    public function defaultStudentAttendance()
    {
    
        $student_data = DB::table('wbscte_other_diploma_student_master_tbl')->select('student_id_pk', 'student_reg_no', 'student_fullname', 'student_course_id', 'student_institute_name', 'student_institute_code', 'student_inst_id', 'student_semester','student_session_yr')
            ->where(['student_is_enrolled' => 1, 'student_exam_fees_status' => 1, 'student_session_yr' => '2022-23', 'student_semester' => 'Semester I',])
            ->get();

        if (sizeof($student_data) > 0) {
            foreach ($student_data as $key => $student) {
              

                $papers = $this->courseWisePaper($student->student_course_id, $student->student_reg_no, $student->student_inst_id, $student->student_semester,$student->student_session_yr);

                if (sizeof($papers) > 0) {

                    foreach ($papers as $key => $paper) {
                        DB::table('wbscte_other_diploma_attendence_tbl')->insert([
                            'att_reg_no' => $student->student_reg_no,
                            'att_inst_id' => $student->student_inst_id,
                            'att_course_id' => $student->student_course_id,
                            'att_paper_id' => $paper->paper_id_pk,
                            'att_sem' => $student->student_semester,
                            'att_paper_type' => $paper->paper_category,
                            'att_paper_entry_type' => 1,
                            'att_is_present' => '1',
                            'att_is_absent' => '0',
                            'att_is_ra' => '0',
                            'att_created_on' => now(),
                            'is_final_submit' => 0,
                            'attr_sessional_yr' => $student->student_session_yr,
                        ]);

                        DB::table('wbscte_other_diploma_attendence_tbl')->insert([
                            'att_reg_no' => $student->student_reg_no,
                            'att_inst_id' => $student->student_inst_id,
                            'att_course_id' => $student->student_course_id,
                            'att_paper_id' => $paper->paper_id_pk,
                            'att_sem' => $student->student_semester,
                            'att_paper_type' => $paper->paper_category,
                            'att_paper_entry_type' => 2,
                            'att_is_present' => '1',
                            'att_is_absent' => '0',
                            'att_is_ra' => '0',
                            'att_created_on' => now(),
                            'is_final_submit' => 0,
                            'attr_sessional_yr' =>$student->student_session_yr,
                        ]);
                        
                        
                        // $searchAttributes1 = [
                        //     'att_reg_no' => $student->student_reg_no,
                        //     'att_inst_id' => $student->student_inst_id,
                        //     'att_course_id' => $student->student_course_id,
                        //     'att_paper_id' => $paper->paper_id_pk,
                        //     'att_sem' => $student->student_semester,
                        //     'att_paper_type' => $paper->paper_category,
                        //     'att_paper_entry_type' => 1,
                        //     'attr_sessional_yr' => $student->student_session_yr,
                        //     // 'is_final_submit'=> 1 

                        // ];
                     
                        // $existingRecord1 = Attendance::where($searchAttributes1)->first();
                        // $attributes1 = [
                        //     'att_is_present' => '1',
                        //     'att_is_absent' => '0',
                        //     'att_is_ra' => '0',
                        //     'att_created_on' => now(),
                        //     'is_final_submit' => $existingRecord1 ? ($existingRecord1->is_final_submit == 0 ? 1 : $existingRecord1->is_final_submit) : 0,
                        //     'attr_sessional_yr' => $student->student_session_yr,
                        // ];
                        // if ($existingRecord1) {
                        //     if ($existingRecord1->is_final_submit == 0) {
                        //         DB::table('wbscte_other_diploma_attendence_tbl')->insert(array_merge($searchAttributes1, $attributes1));
                        //     }
                        // } 
                        // $searchAttributes2 = array_merge($searchAttributes1, [
                        //     'att_paper_entry_type' => 2,
                        // ]);
                        // $existingRecord2 = Attendance::where($searchAttributes2)->first();
                        // $attributes2 = [
                        //     'att_is_present' => '1',
                        //     'att_is_absent' => '0',
                        //     'att_is_ra' => '0',
                        //     'att_created_on' => now(),
                        //     'is_final_submit' => $existingRecord2 ? ($existingRecord2->is_final_submit == 0 ? 1 : $existingRecord2->is_final_submit) : 0,
                        //     'attr_sessional_yr' => $student->student_session_yr,
                        // ];
                        // if ($existingRecord1) {
                        //     if ($existingRecord1->is_final_submit == 0) {
                        //         DB::table('wbscte_other_diploma_attendence_tbl')->insert(array_merge($searchAttributes1, $attributes1));
                        //     }
                        // } 
                        // dd($existingRecord1);
                        // if(!empty($searchAttributes1)){
                           
                            

                        // }
                        
                     
                        // $updateOrCreateAttributes1 = [
                        //     'att_is_present' => '1',
                        //     'att_is_absent' => '0',
                        //     'att_is_ra' => '0',
                        //     'att_created_on' => now(),
                        //     'is_final_submit' => 0,
                        //     'attr_sessional_yr' => $student->student_session_yr,
                        // ];
                        // Attendance::updateOrCreate($searchAttributes1, $updateOrCreateAttributes1);
                        
                        
                        // $searchAttributes2 = array_merge($searchAttributes1, [
                        //     'att_paper_entry_type' => 2, 
                        // ]);
                        // $updateOrCreateAttributes2 = [
                        //     'att_is_present' => '1',
                        //     'att_is_absent' => '0',
                        //     'att_is_ra' => '0',
                        //     'att_created_on' => now(),
                        //     'is_final_submit' => 0,
                        //     'attr_sessional_yr' => $student->student_session_yr,
                        // ];
                        // Attendance::updateOrCreate($searchAttributes2, $updateOrCreateAttributes2);

                        
                        
                    }
                }
            }
        }

        try {

            return response()->json(
                array(
                    'error' => false,
                    'message' => 'All attendance data inserted'
                )
            );
        } catch (\Exception $e) {

            return response()->json(
                array(
                    'error' => true,
                    'code' =>    'INT_00001',
                    'message' => $e->getMessage()
                )
            );
        }
    }
    public function courseWisePaper($course_id, $reg_no, $inst_id, $semester,$session_yr)
    {
        $paperList = DB::table('wbscte_other_diploma_paper_master')
            ->select('paper_id_pk', 'paper_category', 'paper_code', 'paper_name', 'paper_category')
            ->where(['is_active' => 1, 'course_id' => $course_id, 'inst_id' => $inst_id, 'paper_affiliation_year' => $session_yr, 'paper_semester' => $semester])
            ->orderBy('paper_id_pk', 'asc')
            ->get();

        return $paperList;
    }

    public function resultProcessScript()
    {
        $student_data = DB::table('wbscte_other_diploma_student_master_tbl_test')->select('student_id_pk', 'student_reg_no', 'student_fullname', 'student_course_id', 'student_institute_name', 'student_institute_code', 'student_inst_id', 'student_semester', 'student_session_yr')
            ->where(['student_is_enrolled' => 1, 'student_exam_fees_status' => 1, 'student_session_yr' => '2023-24', 'student_semester' => 'Semester I'])
            ->get();

        if (sizeof($student_data) > 0) {
            foreach ($student_data as $key => $student) {
                $papers[] = $this->courseWisePaper($student->student_course_id, $student->student_reg_no, $student->student_inst_id, $student->student_semester);
                if (sizeof($papers) > 0) {
                    foreach ($papers as $key => $paper) {
                        //$marksEntryData = $this->paperCourseInstWisePutMarksData($student->student_reg_no, $student->student_course_id, $student->student_inst_id, $paper->paper_id_pk, $paper->paper_category, $student->student_semester, $student->student_session_yr);
                    }
                }
            }
            dd($papers);
        }
    }

    public function paperCourseInstWisePutMarksData($reg_no, $course_id, $inst_id, $paper_id, $paper_cat, $semester, $session_yr)
    {
        $marksList = DB::table('wbscte_other_diploma_exam_marks_entry_tbl')
            ->select('id', 'stud_reg_no', 'marks', 'internal_attendance_marks')
            ->where(['is_active' => 1, 'stud_reg_no' => $reg_no, 'course_id' => $course_id, 'inst_id' => $inst_id, 'paper_id' => $paper_id, 'paper_type' => $paper_cat, 'paper_affiliation_year' => $session_yr, 'paper_semester' => $semester])
            ->orderBy('id', 'asc')
            ->get();

        return $marksList;
    }

    //Payment 
  

    public function resultPdf(Request $request)
    {
        $session_yr = $request->session_yr;
        $examYear = $request->examYear;
       
        // $semester = $request->semester;
        $inst_id = $request->inst_id;
        $course_id = $request->course_id;

        // $pdf = Pdf::loadView('exports.results3')->setPaper('a4', 'landscape');

        // return $pdf->stream('results3.pdf');
        // $paper_type = $request->paper_type;
        if ($request->semester == 'I') {
            $semester = 'Semester I';
        } else if ($request->semester == 'II') {
            $semester = 'Semester II';
        } else if ($request->semester == 'III') {
            $semester = 'Semester III';
        } else if ($request->semester == 'IV') {
            $semester = 'Semester IV';
        } else if ($request->semester == 'V') {
            $semester = 'Semester V';
        }
        // dd($semester);

        $institute = Institute::select('inst_sl_pk', 'institute_name', 'institute_code')->find($inst_id);
        $course = Course::select('course_id_pk', 'course_duration', 'course_name', 'course_code')->find($course_id);

        $Thpapers = Paper::select('paper_id_pk', 'paper_name', 'paper_category', 'paper_full_marks', 'paper_pass_marks')->where('paper_semester', $semester)
            ->where('inst_id', $inst_id)
            ->where('paper_category', 1)
            ->where('course_id', $course_id)
            ->where('paper_affiliation_year', $session_yr)
            ->where('is_active', 1)
            ->orderBy('paper_id_pk', 'asc')
            ->get();
        $sessionalPapers =   Paper::select('paper_id_pk', 'paper_name', 'paper_category', 'paper_full_marks', 'paper_pass_marks')->where('paper_semester', $semester)
            ->where('inst_id', $inst_id)
            ->where('paper_category', 2)
            ->where('course_id', $course_id)
            ->where('paper_affiliation_year', $session_yr)
            ->where('is_active', 1)
            ->orderBy('paper_id_pk', 'asc')
            ->get();

        $Papers =   Paper::select('paper_id_pk', 'paper_name', 'paper_category', 'paper_full_marks', 'paper_pass_marks')->where('paper_semester', $semester)
            ->where('inst_id', $inst_id)
            ->where('course_id', $course_id)
            ->where('paper_affiliation_year', $session_yr)
            ->where('is_active', 1)
            ->orderBy('paper_id_pk', 'asc')
            ->get();

        //dd($inst_id,$session_yr,$course_id);

        $result_data = Result::where(['INST_ID' => $inst_id, 'REG_YEAR' => $session_yr, 'COURSE_ID' => $course_id])->with('student:student_reg_no,student_fullname')->orderBy('REG_NO', 'asc')
            ->get();
        // dd($result_data) ;
        if ($result_data->count() > 0) {
           
            $finalList = $result_data->map(function ($single, $key) use ($examYear) {
                // dd(!is_null($single["SUB1_TH_INT_CLASS_TEST_MK_{$examYear}"]) , !is_null($single["SUB1_TH_INT_VIVA_MK_{$examYear}"]));
                return [
                    'sl_no' => $key + 1,
                    'student_reg_no' => $single->student->student_reg_no,
                    'student_name' => $single->student->student_fullname,
                    'sub1_th_int_marks' => 
                    (!is_null($single["SUB1_TH_INT_CLASS_TEST_MK_{$examYear}"]) && 
                    ( !is_null($single["SUB1_TH_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB1_TH_INT_VIVA_MK_{$examYear}"]) )
                ) ? 
                (
                    (int)$single["SUB1_TH_INT_CLASS_TEST_MK_{$examYear}"] + 
                    (is_null($single["SUB1_TH_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB1_TH_INT_VIVA_MK_{$examYear}"])
                ) 
                : '',
                    // (
                    //                         !is_null($single["SUB1_TH_INT_CLASS_TEST_MK_{$examYear}"]) &&  (!is_null($single["SUB1_TH_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB1_TH_INT_VIVA_MK_{$examYear}"]) )
                    //                         //  !is_null($single["SUB1_TH_INT_VIVA_MK_{$examYear}"])
                    //                     ) ? 
                    //                     (
                    //                         (int)$single["SUB1_TH_INT_CLASS_TEST_MK_{$examYear}"] + 
                    //                         (int)$single["SUB1_TH_INT_VIVA_MK_{$examYear}"]
                    //                     ) 
                    //                     : '',
                                        
                    'sub1_th_int_attd_marks' => !is_null($single["SUB1_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB1_TH_INT_ATD_MK_{$examYear}"] : '',
                    'sub1_th_ext_marks' => !is_null($single["SUB1_TH_EXT_MK_{$examYear}"]) ? $single["SUB1_TH_EXT_MK_{$examYear}"] : '',
                    'SUB1_OB_MK' => !is_null($single["SUB1_OB_MK"]) ? $single["SUB1_OB_MK"] : '',

                    'sub2_th_int_marks' => (
                                        !is_null($single["SUB2_TH_INT_CLASS_TEST_MK_{$examYear}"]) && 
                                        (!is_null($single["SUB2_TH_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB2_TH_INT_VIVA_MK_{$examYear}"]) )
                                    ) ? 
                                    (
                                        (int)$single["SUB2_TH_INT_CLASS_TEST_MK_{$examYear}"] + 
                                        (is_null($single["SUB2_TH_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB2_TH_INT_VIVA_MK_{$examYear}"])
                                    ) 
                                    : '',

                    'sub2_th_int_attd_marks' => !is_null($single["SUB2_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB2_TH_INT_ATD_MK_{$examYear}"] : '',
                    'sub2_th_ext_marks' => !is_null($single["SUB2_TH_EXT_MK_{$examYear}"]) ? $single["SUB2_TH_EXT_MK_{$examYear}"] : '',
                    'SUB2_OB_MK' => !is_null($single["SUB2_OB_MK"]) ? $single["SUB2_OB_MK"] : '',

                    'sub3_th_int_marks' => (
                        !is_null($single["SUB3_TH_INT_CLASS_TEST_MK_{$examYear}"]) && 
                        (!is_null($single["SUB3_TH_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB3_TH_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                        (int)$single["SUB3_TH_INT_CLASS_TEST_MK_{$examYear}"] + 
                        (is_null($single["SUB3_TH_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB3_TH_INT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',

                    'sub3_th_int_attd_marks' => !is_null($single["SUB3_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB3_TH_INT_ATD_MK_{$examYear}"] : '',
                    'sub3_th_ext_marks' => !is_null($single["SUB3_TH_EXT_MK_{$examYear}"]) ? $single["SUB3_TH_EXT_MK_{$examYear}"] : '',
                    'SUB3_OB_MK' => !is_null($single["SUB3_OB_MK"]) ? $single["SUB3_OB_MK"] : '',

                    'sub4_th_int_marks' => (
                        !is_null($single["SUB4_TH_INT_CLASS_TEST_MK_{$examYear}"]) && 
                        (!is_null($single["SUB4_TH_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB4_TH_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                        (int)$single["SUB4_TH_INT_CLASS_TEST_MK_{$examYear}"] + 
                        (is_null($single["SUB4_TH_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB4_TH_INT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',

                    'sub4_th_int_attd_marks' => !is_null($single["SUB4_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB4_TH_INT_ATD_MK_{$examYear}"] : '',
                    'sub4_th_ext_marks' => !is_null($single["SUB4_TH_EXT_MK_{$examYear}"]) ? $single["SUB4_TH_EXT_MK_{$examYear}"] : '',
                    'SUB4_OB_MK' => !is_null($single["SUB4_OB_MK"]) ? $single["SUB4_OB_MK"] : '',

                    'sub5_th_int_marks' => (
                        !is_null($single["SUB5_TH_INT_CLASS_TEST_MK_{$examYear}"]) && 
                        (!is_null($single["SUB5_TH_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB5_TH_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                        (int)$single["SUB5_TH_INT_CLASS_TEST_MK_{$examYear}"] + 
                        (is_null($single["SUB5_TH_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB5_TH_INT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',

                    'sub5_th_int_attd_marks' => !is_null($single["SUB5_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB5_TH_INT_ATD_MK_{$examYear}"] : '',
                    'sub5_th_ext_marks' => !is_null($single["SUB5_TH_EXT_MK_{$examYear}"]) ? $single["SUB5_TH_EXT_MK_{$examYear}"] : '',
                    'SUB5_OB_MK' => !is_null($single["SUB5_OB_MK"]) ? $single["SUB5_OB_MK"] : '',
                    // start
                    'sub6_th_int_marks' =>(
                        !is_null($single["SUB6_TH_INT_CLASS_TEST_MK_{$examYear}"]) && 
                        (!is_null($single["SUB6_TH_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB6_TH_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                        (int)$single["SUB6_TH_INT_CLASS_TEST_MK_{$examYear}"] + 
                        (is_null($single["SUB6_TH_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB6_TH_INT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',

                    'sub6_th_int_attd_marks' => !is_null($single["SUB6_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB6_TH_INT_ATD_MK_{$examYear}"] : '',
                    'sub6_ext_session_marks' => !is_null($single["SUB6_SES_EXT_MK_{$examYear}"]) ? $single["SUB6_SES_EXT_MK_{$examYear}"] : '',
                    'SUB6_OB_MK' => !is_null($single["SUB6_OB_MK"]) ? $single["SUB6_OB_MK"] : '',
                    'sub7_th_int_marks' => (
                        !is_null($single["SUB7_TH_INT_CLASS_TEST_MK_{$examYear}"]) && 
                        (!is_null($single["SUB7_TH_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB7_TH_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                        (int)$single["SUB7_TH_INT_CLASS_TEST_MK_{$examYear}"] + 
                        (is_null($single["SUB7_TH_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB7_TH_INT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',

                    'sub7_th_int_attd_marks' => !is_null($single["SUB7_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB7_TH_INT_ATD_MK_{$examYear}"] : '',
                    'sub7_ext_session_marks' => !is_null($single["SUB7_SES_EXT_MK_{$examYear}"]) ? $single["SUB7_SES_EXT_MK_{$examYear}"] : '',
                    'SUB7_OB_MK' => !is_null($single["SUB7_OB_MK"]) ? $single["SUB7_OB_MK"] : '',

                    'sub8_th_int_marks' => (
                        !is_null($single["SUB8_TH_INT_CLASS_TEST_MK_{$examYear}"]) && 
                        (!is_null($single["SUB8_TH_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB8_TH_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                        (int)$single["SUB8_TH_INT_CLASS_TEST_MK_{$examYear}"] + 
                        (is_null($single["SUB8_TH_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB8_TH_INT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',

                    'sub8_th_int_attd_marks' => !is_null($single["SUB8_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB8_TH_INT_ATD_MK_{$examYear}"] : '',
                    'sub8_ext_session_marks' => !is_null($single["SUB8_SES_EXT_MK_{$examYear}"]) ? $single["SUB8_SES_EXT_MK_{$examYear}"] : '',
                    'SUB8_OB_MK' => !is_null($single["SUB8_OB_MK"]) ? $single["SUB8_OB_MK"] : '',
                    //sessional
                    'sub9_sess_int_marks' => (
                        !is_null($single["SUB9_SES_CLASS_TEST_MK_{$examYear}"]) &&  
                        (!is_null($single["SUB9_SES_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB9_SES_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                         (int)$single["SUB9_SES_CLASS_TEST_MK_{$examYear}"] + 
                         (is_null($single["SUB9_SES_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB9_SES_INT_VIVA_MK_{$examYear}"])
                    
                    ) 
                    : '',
                    'sub9_sess_int_attd_marks' => !is_null($single["SUB9_SES_INT_ATD_MK_{$examYear}"]) ? $single["SUB9_SES_INT_ATD_MK_{$examYear}"] : '',
                    'sub9_ext_session_marks' => 
                    (
                        !is_null($single["SUB9_SES_EXT_ASS_MK_{$examYear}"]) && 
                        (!is_null($single["SUB9_SES_EXT_VIVA_MK_{$examYear}"]) || is_null($single["SUB9_SES_EXT_VIVA_MK_{$examYear}"]))
                      
                    ) ? 
                    (
                        (int)$single["SUB9_SES_EXT_ASS_MK_{$examYear}"] + 
                        (is_null($single["SUB9_SES_EXT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB9_SES_EXT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',
                    'SUB9_OB_MK' => !is_null($single["SUB9_OB_MK"]) ? $single["SUB9_OB_MK"] : '',
                    'sub10_sess_int_marks' =>  (
                        !is_null($single["SUB10_SES_CLASS_TEST_MK_{$examYear}"]) &&  
                        (!is_null($single["SUB10_SES_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB10_SES_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                         (int)$single["SUB10_SES_CLASS_TEST_MK_{$examYear}"] + 
                         (is_null($single["SUB10_SES_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB10_SES_INT_VIVA_MK_{$examYear}"])
                    
                    ) 
                    : '',
                    'sub10_sess_int_attd_marks' => !is_null($single["SUB10_SES_INT_ATD_MK_{$examYear}"]) ? $single["SUB10_SES_INT_ATD_MK_{$examYear}"] : '',
                    'sub10_ext_session_marks' => (
                        !is_null($single["SUB10_SES_EXT_ASS_MK_{$examYear}"]) && 
                        (!is_null($single["SUB10_SES_EXT_VIVA_MK_{$examYear}"]) || is_null($single["SUB10_SES_EXT_VIVA_MK_{$examYear}"]))
                      
                    ) ? 
                    (
                        (int)$single["SUB10_SES_EXT_ASS_MK_{$examYear}"] + 
                        (is_null($single["SUB10_SES_EXT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB10_SES_EXT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',
                    'SUB10_OB_MK' => !is_null($single["SUB10_OB_MK"]) ? $single["SUB10_OB_MK"] : '',
                    'sub11_sess_int_marks' =>  (
                        !is_null($single["SUB11_SES_CLASS_TEST_MK_{$examYear}"]) &&  
                        (!is_null($single["SUB11_SES_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB11_SES_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                         (int)$single["SUB11_SES_CLASS_TEST_MK_{$examYear}"] + 
                         (is_null($single["SUB11_SES_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB11_SES_INT_VIVA_MK_{$examYear}"])
                    
                    ) 
                    : '',
                    'sub11_sess_int_attd_marks' => !is_null($single["SUB11_SES_INT_ATD_MK_{$examYear}"]) ? $single["SUB11_SES_INT_ATD_MK_{$examYear}"] : '',
                    'sub11_ext_session_marks' =>(
                        !is_null($single["SUB11_SES_EXT_ASS_MK_{$examYear}"]) && 
                        (!is_null($single["SUB11_SES_EXT_VIVA_MK_{$examYear}"]) || is_null($single["SUB11_SES_EXT_VIVA_MK_{$examYear}"]))
                      
                    ) ? 
                    (
                        (int)$single["SUB11_SES_EXT_ASS_MK_{$examYear}"] + 
                        (is_null($single["SUB11_SES_EXT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB11_SES_EXT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',
                    'SUB11_OB_MK' => !is_null($single["SUB11_OB_MK"]) ? $single["SUB11_OB_MK"] : '',
                    'sub12_sess_int_marks' => (
                        !is_null($single["SUB12_SES_CLASS_TEST_MK_{$examYear}"]) &&  
                        (!is_null($single["SUB12_SES_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB12_SES_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                         (int)$single["SUB12_SES_CLASS_TEST_MK_{$examYear}"] + 
                         (is_null($single["SUB12_SES_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB12_SES_INT_VIVA_MK_{$examYear}"])
                    
                    ) 
                    : '',
                    'sub12_sess_int_attd_marks' => !is_null($single["SUB12_SES_INT_ATD_MK_{$examYear}"]) ? $single["SUB12_SES_INT_ATD_MK_{$examYear}"] : '',
                    'sub12_ext_session_marks' => (
                        !is_null($single["SUB12_SES_EXT_ASS_MK_{$examYear}"]) && 
                        (!is_null($single["SUB12_SES_EXT_VIVA_MK_{$examYear}"]) || is_null($single["SUB12_SES_EXT_VIVA_MK_{$examYear}"]))
                      
                    ) ? 
                    (
                        (int)$single["SUB12_SES_EXT_ASS_MK_{$examYear}"] + 
                        (is_null($single["SUB12_SES_EXT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB12_SES_EXT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',
                    'SUB12_OB_MK' => !is_null($single["SUB12_OB_MK"]) ? $single["SUB12_OB_MK"] : '',
                    'sub13_sess_int_marks' =>  (
                        !is_null($single["SUB13_SES_CLASS_TEST_MK_{$examYear}"]) &&  
                        (!is_null($single["SUB13_SES_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB13_SES_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                         (int)$single["SUB13_SES_CLASS_TEST_MK_{$examYear}"] + 
                         (is_null($single["SUB13_SES_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB13_SES_INT_VIVA_MK_{$examYear}"])
                    
                    ) 
                    : '',
                    'sub13_sess_int_attd_marks' => !is_null($single["SUB13_SES_INT_ATD_MK_{$examYear}"]) ? $single["SUB13_SES_INT_ATD_MK_{$examYear}"] : '',
                    'sub13_ext_session_marks' =>(
                        !is_null($single["SUB13_SES_EXT_ASS_MK_{$examYear}"]) && 
                        (!is_null($single["SUB13_SES_EXT_VIVA_MK_{$examYear}"]) || is_null($single["SUB13_SES_EXT_VIVA_MK_{$examYear}"]))
                      
                    ) ? 
                    (
                        (int)$single["SUB13_SES_EXT_ASS_MK_{$examYear}"] + 
                        (is_null($single["SUB13_SES_EXT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB13_SES_EXT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',
                    'SUB13_OB_MK' => !is_null($single["SUB13_OB_MK"]) ? $single["SUB13_OB_MK"] : '',
                    'sub14_sess_int_marks' =>  (
                        !is_null($single["SUB14_SES_CLASS_TEST_MK_{$examYear}"]) &&  
                        (!is_null($single["SUB14_SES_INT_VIVA_MK_{$examYear}"]) || is_null($single["SUB14_SES_INT_VIVA_MK_{$examYear}"]) )
                    ) ? 
                    (
                         (int)$single["SUB14_SES_CLASS_TEST_MK_{$examYear}"] + 
                         (is_null($single["SUB14_SES_INT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB14_SES_INT_VIVA_MK_{$examYear}"])
                    
                    ) 
                    : '',
                    'sub14_sess_int_attd_marks' => !is_null($single["SUB14_SES_INT_ATD_MK_{$examYear}"]) ? $single["SUB14_SES_INT_ATD_MK_{$examYear}"] : '',
                    'sub14_ext_session_marks' =>(
                        !is_null($single["SUB14_SES_EXT_ASS_MK_{$examYear}"]) &&
                        (!is_null($single["SUB14_SES_EXT_VIVA_MK_{$examYear}"]) || is_null($single["SUB14_SES_EXT_VIVA_MK_{$examYear}"]))
                    ) ? 
                    (
                        (int)$single["SUB14_SES_EXT_ASS_MK_{$examYear}"] + 
                        (is_null($single["SUB14_SES_EXT_VIVA_MK_{$examYear}"]) ? 0 : (int)$single["SUB14_SES_EXT_VIVA_MK_{$examYear}"])
                    ) 
                    : '',
                    'SUB14_OB_MK' => !is_null($single["SUB14_OB_MK"]) ? $single["SUB14_OB_MK"] : '',

                    'GRAND_TOTAL' => !is_null($single["GRAND_TOTAL"]) ? $single["GRAND_TOTAL"] : '',
                    'RESULT' => $single["RESULT"] == 'Q' ? 'Pass' : ($single["RESULT"] == 'B' ? 'Back' : 'Fail'),
                    'SUB1_ID' => !is_null($single["SUB1_ID"]) ? $single["SUB1_ID"] : NULL,
                    'SUB2_ID' => !is_null($single["SUB2_ID"]) ? $single["SUB2_ID"] : NULL,
                    'SUB3_ID' => !is_null($single["SUB3_ID"]) ? $single["SUB3_ID"] : NULL,
                    'SUB4_ID' => !is_null($single["SUB4_ID"]) ? $single["SUB4_ID"] : NULL,
                    'SUB5_ID' => !is_null($single["SUB5_ID"]) ? $single["SUB5_ID"] : NULL,
                    'SUB6_ID' => !is_null($single["SUB6_ID"]) ? $single["SUB6_ID"] : NULL,
                    'SUB7_ID' => !is_null($single["SUB7_ID"]) ? $single["SUB7_ID"] : NULL,
                    'SUB8_ID' => !is_null($single["SUB8_ID"]) ? $single["SUB8_ID"] : NULL,
                    'SUB9_ID' => !is_null($single["SUB9_ID"]) ? $single["SUB9_ID"] : NULL,
                    'SUB10_ID' => !is_null($single["SUB10_ID"]) ? $single["SUB10_ID"] : NULL,
                    'SUB11_ID' => !is_null($single["SUB11_ID"]) ? $single["SUB11_ID"] : NULL,
                    'SUB12_ID' => !is_null($single["SUB12_ID"]) ? $single["SUB12_ID"] : NULL,
                    'SUB13_ID' => !is_null($single["SUB13_ID"]) ? $single["SUB13_ID"] : NULL,
                    'SUB14_ID' => !is_null($single["SUB14_ID"]) ? $single["SUB14_ID"] : NULL,
                ];
                
            });
            // dd($finalList);
             
            // dd($finalList->first()["SUB1_TH_INT_CLASS_TEST_MK_{$examYear}"]);
          
        } else {
            $finalList = [];
        }

        $th_cnt = $ses_cnt = 0;

        if (sizeof($Thpapers) > 0) {
            $th_cnt = sizeof($Thpapers);
        }
        if (sizeof($sessionalPapers) > 0) {
            $ses_cnt = sizeof($sessionalPapers);
        }
        // when th count is 4 and no sessional 
        if ($th_cnt == 4 && $ses_cnt == 0) {
            $viewName = 'exports.results';
        }
        // When th count is 5 and sessional count is 3 
        elseif ($th_cnt == 5 && $ses_cnt == 3) {
            $viewName = 'exports.results2';
        } elseif ($th_cnt == 4 && $ses_cnt == 3) {
            $viewName = 'exports.results3';
        } elseif ($th_cnt == 5 && $ses_cnt == 0) {
            $viewName = 'exports.results4';
        } elseif ($th_cnt == 6 && $ses_cnt == 8) {
            $viewName = 'exports.results5';
        } elseif ($th_cnt == 8 && $ses_cnt == 6) {
            $viewName = 'exports.results6';
        } elseif ($th_cnt == 7 && $ses_cnt == 7) {
            $viewName = 'exports.results7';
        } else {
            $viewName = '';
        }
        // dd($viewName);
        if (!empty($viewName) && sizeof($finalList) > 0) {
            if (sizeof($Papers) > 0 && sizeof($Thpapers) > 0) {
                $pdf = Pdf::loadView($viewName, [
                    'papers' => $Papers,
                    'theory_papers' => $Thpapers,
                    'sessional_papers' => $sessionalPapers,
                    'examYear' => $examYear,
                    'semester' => $semester,
                    'institute_name' => $institute->institute_name,
                    'institute_code' => $institute->institute_code,
                    'course_name' => $course->course_name,
                    'course_duration' => $course->course_duration,
                    'course_code' => $course->course_code,
                    'students' => $finalList,
                    'th_cnt' => $th_cnt,
                    'ses_cnt' => $ses_cnt
                ]);

                // return view ($viewName,[
                //     'papers' => $Papers,
                //     'theory_papers' => $Thpapers,
                //     'sessional_papers' => $sessionalPapers,
                //     'examYear' => $examYear,
                //     'semester' => $semester,
                //     'institute_name' => $institute->institute_name,
                //     'institute_code' => $institute->institute_code,
                //     'course_name' => $course->course_name,
                //     'course_duration' => $course->course_duration,
                //     'course_code' => $course->course_code,
                //     'students' => $finalList,
                //     'th_cnt' => $th_cnt,
                //     'ses_cnt' => $ses_cnt
                // ]);
                
                return $pdf->setPaper('a4', 'landscape')
                    ->setOption(['defaultFont' => 'sans-serif'])
                    ->stream($viewName . '.pdf');
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No Data available'
                );
                return response(json_encode($reponse), 200);
            }
        } else {

            $reponse = array(
                'error'     =>  true,
                'message'   =>  'No Data available'
            );
            return response(json_encode($reponse), 200);
        }
    }
    public function applicationFeesPdf(Request $request,$form_num)
    {
        $students = Student::where(['student_form_num' =>$form_num ])->first();
        $institute = Institute::select('inst_sl_pk', 'institute_name', 'institute_code')->find($students->student_inst_id);
        $course = Course::select('course_id_pk', 'course_duration', 'course_name', 'course_code')->find($students->student_course_id);
        $payment = PaymentTransaction::where([
            'initiated_by' =>$form_num,
            'paying_for'=>'APPLICATION'
        ])->first();
        // dd($payment);
        $pdf = Pdf::loadView('exports.application-fees', [
            'students' => $students,
            'institute_name' => $institute->institute_name,
            'institute_code' => $institute->institute_code,
            'course_name' => $course->course_name,
            'course_duration' => $course->course_duration,
            'course_code' => $course->course_code,
            'payment'=> $payment
          
        ]);
        return $pdf->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'sans-serif'])
            ->stream('application-fees.pdf');
       
    }
    public function registrationPdf(Request $request)
    {
            $pdf = Pdf::loadView('exports.registration')->setPaper('a4', 'landscape');

            return $pdf->stream('registration.pdf');

    }
    public function registrationFeesPdf(Request $request,$form_num)
    { 
        $students = Student::where(['student_form_num' =>$form_num ])->first();
        $institute = Institute::select('inst_sl_pk', 'institute_name', 'institute_code')->find($students->student_inst_id);
        //dd($institute);
        $course = Course::select('course_id_pk', 'course_duration', 'course_name', 'course_code')->find($students->student_course_id);
        $payment = PaymentTransaction::where([
            'initiated_by' =>$form_num,
            'paying_for'=>'REGISTRATION'
        ])->first();
        $pdf = Pdf::loadView('exports.registration-fees', [
            'students' => $students,
            'institute_name' => $institute->institute_name,
            'institute_code' => $institute->institute_code,
            'course_name' => $course->course_name,
            'course_duration' => $course->course_duration,
            'course_code' => $course->course_code,
            'payment'=> $payment
          
        ]);
       
        return $pdf->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'sans-serif'])
            ->stream('registration-fees.pdf');
    }
    public  function studentDetailsPdf(Request $request,$form_num)
    {
        $students = Student::where(['student_form_num' =>$form_num ])->first();
        $institute = Institute::select('inst_sl_pk', 'institute_name', 'institute_code')->find($students->student_inst_id);
        $course = Course::select('course_id_pk', 'course_duration', 'course_name', 'course_code')->find($students->student_course_id);
        $payments = PaymentTransaction::where([
            'initiated_by' => $form_num
        ])->whereIn('paying_for', ['APPLICATION', 'REGISTRATION'])->get();
        // dd($payments);
        $paymentDetails = $payments->map(function ($payment) {
            return [
                'paying_for' => $payment->paying_for,
                'amount' => $payment->trans_amount,
                'status' => $payment->trans_status,
                'transaction_id' => $payment->trans_id,
                'status' => $payment->trans_status,
                'trans_date' => $payment->trans_time 
                // 'trans_amount' => $payment->trans_amount,
            ];
        })->toArray();
        // dd($paymentDetails);
        $pdf = Pdf::loadView('exports.student-details', [
            'students' => $students,
            'institute_name' => $institute->institute_name,
            'institute_code' => $institute->institute_code,
            'course_name' => $course->course_name,
            'course_duration' => $course->course_duration,
            'course_code' => $course->course_code,
            'payment'=> $paymentDetails
          
        ]);
        return $pdf->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'sans-serif'])
            ->stream('student-details.pdf');
    }


    public function hsvocOnePdf(Request $request)
    {
        $pdf = Pdf::loadView('hsvoc_one')->setPaper('a4', 'landscape');

         return $pdf->stream('hsvocone.pdf');

    }
}
