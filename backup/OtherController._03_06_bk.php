<?php

namespace App\Http\Controllers\wbscte;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\PaymentLib\AESEncDec;
use App\Models\wbscte\Paper;
use App\Models\wbscte\Institute;
use App\Models\wbscte\Course;
use App\Models\wbscte\Result;
use Barryvdh\DomPDF\Facade\Pdf;

class OtherController extends Controller
{
    public function defaultStudentAttendance()
    {
        $student_data = DB::table('wbscte_other_diploma_student_master_tbl')->select('student_id_pk', 'student_reg_no', 'student_fullname', 'student_course_id', 'student_institute_name', 'student_institute_code', 'student_inst_id', 'student_semester')
            ->where(['student_is_enrolled' => 1, 'student_exam_fees_status' => 1, 'student_session_yr' => '2023-24', 'student_semester' => 'Semester I'])
            ->get();

        if (sizeof($student_data) > 0) {
            foreach ($student_data as $key => $student) {

                $papers = $this->courseWisePaper($student->student_course_id, $student->student_reg_no, $student->student_inst_id, $student->student_semester);

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
                            'attr_sessional_yr' => '2023-24',
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
                            'attr_sessional_yr' => '2023-24',
                        ]);
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
    public function courseWisePaper($course_id, $reg_no, $inst_id, $semester)
    {
        $paperList = DB::table('wbscte_other_diploma_paper_master')
            ->select('paper_id_pk', 'paper_category', 'paper_code', 'paper_name', 'paper_category')
            ->where(['is_active' => 1, 'course_id' => $course_id, 'inst_id' => $inst_id, 'paper_affiliation_year' => '2023-24', 'paper_semester' => $semester])
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
    public function payment()
    {
        //1001954 || 1000605
        $orderid = '';
        for ($i = 0; $i < 10; $i++) {
            $d = rand(1, 30) % 2;
            $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
            $orderid = $orderid . $d;
        }
        $base_url = env('APP_URL') . '/payment/';
        $success_url = $base_url . 'success';
        //echo $success_url;
        $fail_url = $base_url . 'fail';
        $key = "pWhMnIEMc4q6hKdi2Fx50Ii8CKAoSIqv9ScSpwuMHM4=";
        $other            =    "OD";
        $marid =  '5';
        $merchant_order_num = $orderid;
        $total_amount = 500.00;
        $requestParameter  = "1000605|DOM|IN|INR|" . $total_amount . "|" . $other . "|" . $success_url . "|" . $fail_url . "|SBIEPAY|" . $merchant_order_num . "|" . $marid . "|NB|ONLINE|ONLINE,pWhMnIEMc4q6hKdi2Fx50Ii8CKAoSIqv9ScSpwuMHM4=";

        // 1000605|DOM|IN|INR|500|Other|https://council.aranax.tech/diploma/services/public/payment/success|https://council.aranax.tech/diploma/services/public/payment/fail|SBIEPAY|A8ZJ40X3LB|2|NB|ONLINE|ONLINE

        $aes =  new AESEncDec();
        $EncryptTrans = $aes->encrypt($requestParameter, $key);
        $decrypt = $aes->decrypt($EncryptTrans, $key);
        // echo $EncryptTrans . '=====' . $decrypt;
        // die();

        $merchIdVal = '1000605';
        return view('test', compact('EncryptTrans', 'merchIdVal'));
    }
    public function paymentSuccess(Request $request)
    {
        $key = "pWhMnIEMc4q6hKdi2Fx50Ii8CKAoSIqv9ScSpwuMHM4=";
        //dd($request->all());
        $aes =  new AESEncDec();
        // $EncryptTrans = $aes->encrypt($requestParameter, $key);
        $decrypt = $aes->decrypt($request->encData, $key);
        dd($decrypt);
    }

    public function paymentFail(Request $request)
    {
        $key = "pWhMnIEMc4q6hKdi2Fx50Ii8CKAoSIqv9ScSpwuMHM4=";
        //dd($request->all());
        $aes =  new AESEncDec();
        // $EncryptTrans = $aes->encrypt($requestParameter, $key);
        $decrypt = $aes->decrypt($request->encData, $key);
        dd($decrypt);
    }

    public function resultPdf(Request $request)
    {

        $session_yr = $request->session_yr;
        $examYear = $request->examYear;
        $semester = $request->semester;
        $inst_id = $request->inst_id;
        $course_id = $request->course_id;
        //$paper_type = $request->paper_type;

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

        $result_data = Result::where(['INST_ID' => $inst_id, 'REG_YEAR' => $session_yr, 'COURSE_ID' => $course_id])->with('student:student_reg_no,student_fullname')->orderBy('SL', 'asc')
            ->get();
        //return $result_data;
        $finalList = $result_data->map(function ($single, $key) use ($examYear) {

            return [
                'sl_no' => $key + 1,
                'student_reg_no' => $single->student->student_reg_no,
                'student_name' => $single->student->student_fullname,
                'sub1_th_int_marks' => !is_null($single["SUB1_TH_INT_MK_{$examYear}"]) ? $single["SUB1_TH_INT_MK_{$examYear}"] : '',
                'sub1_th_int_attd_marks' => !is_null($single["SUB1_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB1_TH_INT_ATD_MK_{$examYear}"] : '',
                'sub1_th_ext_marks' => !is_null($single["SUB1_TH_EXT_MK_{$examYear}"]) ? $single["SUB1_TH_EXT_MK_{$examYear}"] : '',
                'SUB1_OB_MK' => !is_null($single["SUB1_OB_MK"]) ? $single["SUB1_OB_MK"] : '',
                'sub2_th_int_marks' => !is_null($single["SUB2_TH_INT_MK_{$examYear}"]) ? $single["SUB2_TH_INT_MK_{$examYear}"] : '',
                'sub2_th_int_attd_marks' => !is_null($single["SUB2_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB2_TH_INT_ATD_MK_{$examYear}"] : '',
                'sub2_th_ext_marks' => !is_null($single["SUB2_TH_EXT_MK_{$examYear}"]) ? $single["SUB2_TH_EXT_MK_{$examYear}"] : '',
                'SUB2_OB_MK' => !is_null($single["SUB2_OB_MK"]) ? $single["SUB2_OB_MK"] : '',
                'sub3_th_int_marks' => !is_null($single["SUB3_TH_INT_MK_{$examYear}"]) ? $single["SUB3_TH_INT_MK_{$examYear}"] : '',
                'sub3_th_int_attd_marks' => !is_null($single["SUB3_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB3_TH_INT_ATD_MK_{$examYear}"] : '',
                'sub3_th_ext_marks' => !is_null($single["SUB3_TH_EXT_MK_{$examYear}"]) ? $single["SUB3_TH_EXT_MK_{$examYear}"] : '',
                'SUB3_OB_MK' => !is_null($single["SUB3_OB_MK"]) ? $single["SUB3_OB_MK"] : '',
                'sub4_th_int_marks' => !is_null($single["SUB4_TH_INT_MK_{$examYear}"]) ? $single["SUB4_TH_INT_MK_{$examYear}"] : '',
                'sub4_th_int_attd_marks' => !is_null($single["SUB4_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB4_TH_INT_ATD_MK_{$examYear}"] : '',
                'sub4_th_ext_marks' => !is_null($single["SUB4_TH_EXT_MK_{$examYear}"]) ? $single["SUB4_TH_EXT_MK_{$examYear}"] : '',
                'SUB4_OB_MK' => !is_null($single["SUB4_OB_MK"]) ? $single["SUB4_OB_MK"] : '',
                'sub5_th_int_marks' => !is_null($single["SUB5_TH_INT_MK_{$examYear}"]) ? $single["SUB5_TH_INT_MK_{$examYear}"] : '',
                'sub5_th_int_attd_marks' => !is_null($single["SUB5_TH_INT_ATD_MK_{$examYear}"]) ? $single["SUB5_TH_INT_ATD_MK_{$examYear}"] : '',
                'sub5_th_ext_marks' => !is_null($single["SUB5_TH_EXT_MK_{$examYear}"]) ? $single["SUB5_TH_EXT_MK_{$examYear}"] : '',
                'SUB5_OB_MK' => !is_null($single["SUB5_OB_MK"]) ? $single["SUB5_OB_MK"] : '',
                'sub6_th_int_marks' => !is_null($single["SUB6_SES_INT_MK_{$examYear}"]) ? $single["SUB6_SES_INT_MK_{$examYear}"] : '',
                'sub6_ext_session_marks' => !is_null($single["SUB6_SES_EXT_MK_{$examYear}"]) ? $single["SUB6_SES_EXT_MK_{$examYear}"] : '',
                'SUB6_OB_MK' => !is_null($single["SUB6_OB_MK"]) ? $single["SUB6_OB_MK"] : '',
                'sub7_th_int_marks' => !is_null($single["SUB7_SES_INT_MK_{$examYear}"]) ? $single["SUB7_SES_INT_MK_{$examYear}"] : '',
                'sub7_ext_session_marks' => !is_null($single["SUB7_SES_EXT_MK_{$examYear}"]) ? $single["SUB7_SES_EXT_MK_{$examYear}"] : '',
                'SUB7_OB_MK' => !is_null($single["SUB7_OB_MK"]) ? $single["SUB7_OB_MK"] : '',
                'sub8_th_int_marks' => !is_null($single["SUB8_SES_INT_MK_{$examYear}"]) ? $single["SUB8_SES_INT_MK_{$examYear}"] : '',
                'sub8_ext_session_marks' => !is_null($single["SUB8_SES_EXT_MK_{$examYear}"]) ? $single["SUB8_SES_EXT_MK_{$examYear}"] : '',
                'SUB8_OB_MK' => !is_null($single["SUB8_OB_MK"]) ? $single["SUB8_OB_MK"] : '',
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
            ];
        });
        $th_cnt = $ses_cnt = 0;
        //return $Thpapers;
        if (sizeof($Thpapers) > 0) {
            $th_cnt = sizeof($Thpapers);
        }
        if (sizeof($sessionalPapers) > 0) {
            $ses_cnt = sizeof($sessionalPapers);
        }

        $pdf = Pdf::loadView('exports.results', [
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
        return $pdf->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'sans-serif'])
            ->stream('results.pdf');
    }
}
