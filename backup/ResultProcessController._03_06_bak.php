<?php

namespace App\Http\Controllers\wbscte;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\wbscte\Result;
use App\Models\wbscte\Paper;
use App\PaymentLib\AESEncDec;

class ResultProcessController extends Controller
{

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

        $resultData = Result::with('institute:inst_sl_pk,institute_name', 'course:course_id_pk,course_name,course_code', 'student:student_reg_no,student_fullname,student_semester')->get();
        return  $resultData;

        if (sizeof($resultData) > 0) {
            foreach ($resultData as $key => $student) {
                $papers[] = $this->courseWisePaper($student->COURSE_ID, $student->REG_NO, $student->INST_ID, $student->student->student_semester);
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

    //Step 1
    public function resultProcessScriptStep1(Request $request)
    {
        $year = $request->year;
        $resultData = Result::get();
        $paper = Paper::query();
        //return $resultData;
        if (sizeof($resultData) > 0) {
            foreach ($resultData as $key => $row) {

                $SUB1_OB_MK = (int)$row['SUB1_TH_INT_MK_' . $year . ''] + (int)$row['SUB1_TH_INT_ATD_MK_' . $year . ''] + (int)$row['SUB1_TH_EXT_MK_' . $year . ''];
                $SUB2_OB_MK = (int)$row['SUB2_TH_INT_MK_' . $year . ''] + (int)$row['SUB2_TH_INT_ATD_MK_' . $year . ''] + (int)$row['SUB2_TH_EXT_MK_' . $year . ''];
                $SUB3_OB_MK = (int)$row['SUB3_TH_INT_MK_' . $year . ''] + (int)$row['SUB3_TH_INT_ATD_MK_' . $year . ''] + (int)$row['SUB3_TH_EXT_MK_' . $year . ''];
                $SUB4_OB_MK = (int)$row['SUB4_TH_INT_MK_' . $year . ''] + (int)$row['SUB4_TH_INT_ATD_MK_' . $year . ''] + (int)$row['SUB4_TH_EXT_MK_' . $year . ''];
                $SUB5_OB_MK = (int)$row['SUB5_TH_INT_MK_' . $year . ''] + (int)$row['SUB5_TH_INT_ATD_MK_' . $year . ''] + (int)$row['SUB5_TH_EXT_MK_' . $year . ''];
                $SUB6_OB_MK = (int)$row['SUB6_SES_INT_MK_' . $year . ''] + (int)$row['SUB6_SES_EXT_MK_' . $year . ''];
                $SUB7_OB_MK = (int)$row['SUB7_SES_INT_MK_' . $year . ''] + (int)$row['SUB7_SES_EXT_MK_' . $year . ''];
                $SUB8_OB_MK = (int)$row['SUB8_SES_INT_MK_' . $year . ''] + (int)$row['SUB8_SES_EXT_MK_' . $year . ''];



                $SUB1_FL_MK = (!is_null($row['SUB1_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB1_ID']) : NULL;
                $SUB2_FL_MK = (!is_null($row['SUB2_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB2_ID']) : NULL;
                $SUB3_FL_MK = (!is_null($row['SUB3_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB3_ID']) : NULL;
                $SUB4_FL_MK = (!is_null($row['SUB4_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB4_ID']) : NULL;
                $SUB5_FL_MK = (!is_null($row['SUB5_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB5_ID']) : NULL;
                $SUB6_FL_MK = (!is_null($row['SUB6_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB6_ID']) : NULL;
                $SUB7_FL_MK = (!is_null($row['SUB7_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB7_ID']) : NULL;
                $SUB8_FL_MK = (!is_null($row['SUB8_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB8_ID']) : NULL;

                $GRAND_TOTAL = $SUB1_OB_MK + $SUB2_OB_MK + $SUB3_OB_MK + $SUB4_OB_MK + $SUB5_OB_MK + $SUB6_OB_MK + $SUB7_OB_MK + $SUB8_OB_MK;

                Result::where('REG_NO', $row['REG_NO'])
                    ->update([
                        'SUB1_FL_MK' => !is_null($SUB1_FL_MK) ? $SUB1_FL_MK->paper_full_marks : NULL,
                        'SUB2_FL_MK' => !is_null($SUB2_FL_MK) ? $SUB2_FL_MK->paper_full_marks : NULL,
                        'SUB3_FL_MK' => !is_null($SUB3_FL_MK) ? $SUB3_FL_MK->paper_full_marks : NULL,
                        'SUB4_FL_MK' => !is_null($SUB4_FL_MK) ? $SUB4_FL_MK->paper_full_marks : NULL,
                        'SUB5_FL_MK' => !is_null($SUB5_FL_MK) ? $SUB5_FL_MK->paper_full_marks : NULL,
                        'SUB6_FL_MK' => !is_null($SUB6_FL_MK) ? $SUB6_FL_MK->paper_full_marks : NULL,
                        'SUB7_FL_MK' => !is_null($SUB7_FL_MK) ? $SUB7_FL_MK->paper_full_marks : NULL,
                        'SUB8_FL_MK' => !is_null($SUB8_FL_MK) ? $SUB8_FL_MK->paper_full_marks : NULL,
                        'SUB1_OB_MK' => $SUB1_OB_MK,
                        'SUB2_OB_MK' => $SUB2_OB_MK,
                        'SUB3_OB_MK' => $SUB3_OB_MK,
                        'SUB4_OB_MK' => $SUB4_OB_MK,
                        'SUB5_OB_MK' => $SUB5_OB_MK,
                        'SUB6_OB_MK' => $SUB6_OB_MK,
                        'SUB7_OB_MK' => $SUB7_OB_MK,
                        'SUB8_OB_MK' => $SUB8_OB_MK,
                        'SUB1_APPR_YR' => $year,
                        'SUB2_APPR_YR' => $year,
                        'SUB3_APPR_YR' => $year,
                        'SUB4_APPR_YR' => $year,
                        'SUB5_APPR_YR' => $year,
                        'SUB6_APPR_YR' => $year,
                        'SUB7_APPR_YR' => $year,
                        'SUB8_APPR_YR' => $year,
                        'EXAM_YEAR' => $year,
                        'GRAND_TOTAL' => $GRAND_TOTAL
                    ]);
            }
            echo "Updated Successfully";
        }
    }

    //Step 2
    public function resultProcessScriptStep2(Request $request)
    {
        $year = $request->year;
        $resultData = Result::orderBy('SL', 'asc')->get();
        $paper = Paper::query();
        if (sizeof($resultData) > 0) {
            foreach ($resultData as $key => $row) {
                $SUB1_PASS_MK = (!is_null($row['SUB1_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB1_ID']) : NULL;
                $SUB2_PASS_MK = (!is_null($row['SUB2_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB2_ID']) : NULL;
                $SUB3_PASS_MK = (!is_null($row['SUB3_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB3_ID']) : NULL;
                $SUB4_PASS_MK = (!is_null($row['SUB4_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB4_ID']) : NULL;
                $SUB5_PASS_MK = (!is_null($row['SUB5_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB5_ID']) : NULL;
                $SUB6_PASS_MK = (!is_null($row['SUB6_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB6_ID']) : NULL;
                $SUB7_PASS_MK = (!is_null($row['SUB7_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB7_ID']) : NULL;
                $SUB8_PASS_MK = (!is_null($row['SUB8_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB8_ID']) : NULL;

                $SUB1_FL_MK = (!is_null($row['SUB1_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB1_ID']) : NULL;
                $SUB2_FL_MK = (!is_null($row['SUB2_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB2_ID']) : NULL;
                $SUB3_FL_MK = (!is_null($row['SUB3_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB3_ID']) : NULL;
                $SUB4_FL_MK = (!is_null($row['SUB4_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB4_ID']) : NULL;
                $SUB5_FL_MK = (!is_null($row['SUB5_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB5_ID']) : NULL;
                $SUB6_FL_MK = (!is_null($row['SUB6_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB6_ID']) : NULL;
                $SUB7_FL_MK = (!is_null($row['SUB7_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB7_ID']) : NULL;
                $SUB8_FL_MK = (!is_null($row['SUB8_ID'])) ? $paper->clone()->select('paper_full_marks')->find($row['SUB8_ID']) : NULL;

                $SUB1_OB_MK = (int)$row['SUB1_OB_MK'];
                $SUB2_OB_MK = (int)$row['SUB2_OB_MK'];
                $SUB3_OB_MK = (int)$row['SUB3_OB_MK'];
                $SUB4_OB_MK = (int)$row['SUB4_OB_MK'];
                $SUB5_OB_MK = (int)$row['SUB5_OB_MK'];
                $SUB6_OB_MK = (int)$row['SUB6_OB_MK'];
                $SUB7_OB_MK = (int)$row['SUB7_OB_MK'];
                $SUB8_OB_MK = (int)$row['SUB8_OB_MK'];

                $SUB6_INT_OB_MK = $row['SUB6_SES_INT_MK_' . $year . ''];
                $SUB6_EXT_OB_MK = $row['SUB6_SES_EXT_MK_' . $year . ''];

                $SUB7_INT_OB_MK = $row['SUB7_SES_INT_MK_' . $year . ''];
                $SUB7_EXT_OB_MK = $row['SUB7_SES_EXT_MK_' . $year . ''];

                $SUB8_INT_OB_MK = $row['SUB8_SES_INT_MK_' . $year . ''];
                $SUB8_EXT_OB_MK = $row['SUB8_SES_EXT_MK_' . $year . ''];
                $credit = "";
                //dd($SUB1_OB_MK);
                if (!is_null($SUB1_PASS_MK)) {
                    if ($SUB1_OB_MK >= $SUB1_PASS_MK->paper_pass_marks && !is_null($SUB1_PASS_MK->paper_pass_marks)) {
                        if (!is_null($SUB1_FL_MK)) {

                            $sub1Percent = $this->calculatePercentage($SUB1_FL_MK->paper_full_marks, $SUB1_OB_MK);
                            //dd($sub1Percent);
                            if ($sub1Percent >= 40) {
                                // dd($sub1Percent, $SUB1_FL_MK->paper_full_marks, $SUB1_OB_MK, $row['REG_NO']);
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB1_TOT_RES' => 'Q'
                                    ]);
                                $credit .= "SUB1,";
                            } else {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB1_TOT_RES' => 'X'
                                    ]);
                                $credit .= "";
                            }
                        }
                    } else {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB1_TOT_RES' => 'X'
                            ]);
                        $credit .= "";
                    }
                } else {
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            'SUB1_TOT_RES' => 'X'
                        ]);
                    $credit .= "";
                }
                if (!is_null($SUB2_PASS_MK)) {
                    if ($SUB2_OB_MK >= $SUB2_PASS_MK->paper_pass_marks && !is_null($SUB2_PASS_MK->paper_pass_marks)) {
                        if (!is_null($SUB2_FL_MK)) {
                            $sub2Percent = $this->calculatePercentage($SUB2_FL_MK->paper_full_marks, $SUB2_OB_MK);
                            if ($sub2Percent >= 40) {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB2_TOT_RES' => 'Q'
                                    ]);
                                $credit .= "SUB2,";
                            } else {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB2_TOT_RES' => 'X'
                                    ]);
                                $credit .= "";
                            }
                        }
                    } else {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB2_TOT_RES' => 'X'
                            ]);
                        $credit .= "";
                    }
                } else {
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            'SUB2_TOT_RES' => 'X'
                        ]);
                    $credit .= "";
                }
                if (!is_null($SUB3_PASS_MK)) {
                    if ($SUB3_OB_MK >= $SUB3_PASS_MK->paper_pass_marks && !is_null($SUB3_PASS_MK->paper_pass_marks)) {
                        if (!is_null($SUB3_FL_MK)) {
                            $sub3Percent = $this->calculatePercentage($SUB3_FL_MK->paper_full_marks, $SUB3_OB_MK);
                            if ($sub3Percent >= 40) {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB3_TOT_RES' => 'Q'
                                    ]);
                                $credit .=  "SUB3,";
                            } else {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB3_TOT_RES' => 'X'
                                    ]);
                                $credit .= "";
                            }
                        }
                    } else {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB3_TOT_RES' => 'X'
                            ]);
                        $credit .= "";
                    }
                } else {
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            'SUB3_TOT_RES' => 'X'
                        ]);
                    $credit .= "";
                }
                if (!is_null($SUB4_PASS_MK)) {
                    if ($SUB4_OB_MK >= $SUB4_PASS_MK->paper_pass_marks && !is_null($SUB4_PASS_MK->paper_pass_marks)) {
                        if (!is_null($SUB4_FL_MK)) {
                            $sub4Percent = $this->calculatePercentage($SUB4_FL_MK->paper_full_marks, $SUB4_OB_MK);
                            if ($sub4Percent >= 40) {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB4_TOT_RES' => 'Q'
                                    ]);
                                $credit .= "SUB4,";
                            } else {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB4_TOT_RES' => 'X'
                                    ]);
                                $credit .= "";
                            }
                        }
                    } else {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB4_TOT_RES' => 'X'
                            ]);
                        $credit .= "";
                    }
                } else {
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            'SUB4_TOT_RES' => 'X'
                        ]);
                    $credit .= "";
                }
                if (!is_null($SUB5_PASS_MK)) {
                    if ($SUB5_OB_MK >= $SUB5_PASS_MK->paper_pass_marks && !is_null($SUB5_PASS_MK->paper_pass_marks)) {
                        if (!is_null($SUB5_FL_MK)) {
                            $sub5Percent = $this->calculatePercentage($SUB5_FL_MK->paper_full_marks, $SUB5_OB_MK);
                            if ($sub5Percent >= 40) {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB5_TOT_RES' => 'Q'
                                    ]);
                                $credit .=  "SUB5,";
                            } else {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB5_TOT_RES' => 'X'
                                    ]);
                                $credit .= "";
                            }
                        }
                    } else {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB5_TOT_RES' => 'X'
                            ]);
                        $credit .= "";
                    }
                } else {
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            'SUB5_TOT_RES' => 'X'
                        ]);
                    $credit .= "";
                }
                if (!is_null($SUB6_PASS_MK)) {
                    if ($SUB6_OB_MK >= $SUB6_PASS_MK->paper_pass_marks && !is_null($SUB6_PASS_MK->paper_pass_marks)) {
                        if (!is_null($SUB6_FL_MK)) {
                            $sub6Percent = $this->calculatePercentage($SUB6_FL_MK->paper_full_marks, $SUB6_OB_MK);
                            $sub6InttotMK = $SUB6_FL_MK->paper_full_marks / 2;
                            $sub6ExttotMK = $SUB6_FL_MK->paper_full_marks / 2;
                            $sub6Intpercent = $this->calculatePercentage((int)$sub6InttotMK, $SUB6_INT_OB_MK);
                            $sub6Extpercent = $this->calculatePercentage((int)$sub6ExttotMK, $SUB6_EXT_OB_MK);

                            if ($sub6Extpercent >= 50 && $sub6Intpercent >= 50 && $sub6Percent >= 50) {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB6_TOT_RES' => 'Q'
                                    ]);
                                $credit .=  "SUB6,";
                            } else {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB6_TOT_RES' => 'X'
                                    ]);
                                $credit .= "";
                            }
                        }
                    } else {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB6_TOT_RES' => 'X'
                            ]);
                        $credit .= "";
                    }
                } else {
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            'SUB6_TOT_RES' => 'X'
                        ]);
                    $credit .= "";
                }
                if (!is_null($SUB7_PASS_MK)) {
                    if ($SUB7_OB_MK >= $SUB7_PASS_MK->paper_pass_marks && !is_null($SUB7_PASS_MK->paper_pass_marks)) {
                        if (!is_null($SUB7_FL_MK)) {
                            $sub7Percent = $this->calculatePercentage($SUB7_FL_MK->paper_full_marks, $SUB7_OB_MK);
                            $sub7InttotMK = $SUB7_FL_MK->paper_full_marks / 2;
                            $sub7ExttotMK = $SUB7_FL_MK->paper_full_marks / 2;
                            $sub7Intpercent = $this->calculatePercentage((int)$sub7InttotMK, $SUB7_INT_OB_MK);
                            $sub7Extpercent = $this->calculatePercentage((int)$sub7ExttotMK, $SUB7_EXT_OB_MK);

                            if ($sub7Extpercent >= 50 && $sub7Intpercent >= 50 && $sub7Percent >= 50) {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB7_TOT_RES' => 'Q'
                                    ]);
                                $credit .=  "SUB7,";
                            } else {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB7_TOT_RES' => 'X'
                                    ]);
                                $credit .= "";
                            }
                        }
                    } else {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB7_TOT_RES' => 'X'
                            ]);
                        $credit .= "";
                    }
                } else {
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            'SUB7_TOT_RES' => 'X'
                        ]);
                    $credit .= "";
                }
                if (!is_null($SUB8_PASS_MK)) {
                    if ($SUB8_OB_MK >= $SUB8_PASS_MK->paper_pass_marks && !is_null($SUB8_PASS_MK->paper_pass_marks)) {
                        if (!is_null($SUB8_FL_MK)) {
                            $sub8Percent = $this->calculatePercentage($SUB8_FL_MK->paper_full_marks, $SUB8_OB_MK);
                            $sub8InttotMK = $SUB8_FL_MK->paper_full_marks / 2;
                            $sub8ExttotMK = $SUB8_FL_MK->paper_full_marks / 2;
                            $sub8Intpercent = $this->calculatePercentage((int)$sub8InttotMK, $SUB8_INT_OB_MK);
                            $sub8Extpercent = $this->calculatePercentage((int)$sub8ExttotMK, $SUB8_EXT_OB_MK);

                            if ($sub8Extpercent >= 50 && $sub8Intpercent >= 50 && $sub8Percent >= 50) {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB8_TOT_RES' => 'Q'
                                    ]);
                                $credit .=  "SUB8,";
                            } else {
                                Result::where('REG_NO', $row['REG_NO'])
                                    ->update([
                                        'SUB8_TOT_RES' => 'X'
                                    ]);
                                $credit .= "";
                            }
                        }
                    } else {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB8_TOT_RES' => 'X'
                            ]);
                        $credit .= "";
                    }
                } else {
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            'SUB8_TOT_RES' => 'X'
                        ]);
                    $credit .= "";
                }

                Result::where('REG_NO', $row['REG_NO'])
                    ->update([
                        'CREDIT_' . $year . '' => $credit
                    ]);
            }

            echo 'Updated Successfully';
        } else {
            echo 'No Data Found';
        }
    }

    //Step 3 Apply special grace to all theory paper
    public function resultProcessScriptStep3(Request $request)
    {
        $year = $request->year;
        $resultData = Result::orderBy('SL', 'asc')->get();
        $paper = Paper::query();
        if (sizeof($resultData) > 0) {
            foreach ($resultData as $key => $row) {
                $passed_paper_marks = [];
                $failed_paper_marks = [];
                $gr_avb = 1;
                $SUB1_GR_MK = '';
                $SUB2_GR_MK = '';
                $SUB3_GR_MK = '';
                $SUB4_GR_MK = '';
                $SUB5_GR_MK = '';
                $SUB1_GR = '';
                $SUB2_GR = '';
                $SUB3_GR = '';
                $SUB4_GR = '';
                $SUB5_GR = '';
                //$P1_GR = ''; $P2_GR = ''; $P3_GR = ''; $P4_GR = ''; 
                $splgr_elgb = 1;
                $GT = "";
                $is_abs = 0;
                $is_s1_abs = 0;
                $is_s2_abs = 0;
                $is_s3_abs = 0;
                $is_s4_abs = 0;
                $is_s5_abs = 0;
                $SUB1_PASS_MK = (!is_null($row['SUB1_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB1_ID']) : NULL;
                $SUB2_PASS_MK = (!is_null($row['SUB2_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB2_ID']) : NULL;
                $SUB3_PASS_MK = (!is_null($row['SUB3_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB3_ID']) : NULL;
                $SUB4_PASS_MK = (!is_null($row['SUB4_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB4_ID']) : NULL;
                $SUB5_PASS_MK = (!is_null($row['SUB5_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB5_ID']) : NULL;


                $SUB1_OB_MK = $row['SUB1_OB_MK'];
                $SUB2_OB_MK = $row['SUB2_OB_MK'];
                $SUB3_OB_MK = $row['SUB3_OB_MK'];
                $SUB4_OB_MK = $row['SUB4_OB_MK'];
                $SUB5_OB_MK = $row['SUB5_OB_MK'];
                $credit = "";
                //Special Grace
                if (
                    ($row["SUB1_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB1_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB1_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s1_abs = 1;
                }

                if (
                    ($row["SUB2_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB2_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB2_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s2_abs = 1;
                }

                if (
                    ($row["SUB3_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB3_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB3_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s3_abs = 1;
                }

                if (
                    ($row["SUB4_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB4_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB4_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s4_abs = 1;
                }

                if (
                    ($row["SUB5_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB5_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB5_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s5_abs = 1;
                }

                if (($is_s1_abs == 1) || ($is_s2_abs == 1) || ($is_s3_abs == 1) || ($is_s4_abs == 1) || ($is_s5_abs == 1)) {
                    $is_abs = 1;
                }

                if (($row["SUB1_APPR_YR"] == $year) && ($is_s1_abs == 0)) {
                    if (!is_null($SUB1_PASS_MK) && ($row["SUB1_OB_MK"] < $SUB1_PASS_MK->paper_pass_marks)) {
                        if (($SUB1_PASS_MK->paper_pass_marks - $row["SUB1_OB_MK"]) <= $gr_avb) {
                            $SUB1_GR_MK   =  $SUB1_PASS_MK->paper_pass_marks - $row["SUB1_OB_MK"];
                            //$gr_avb =  $gr_avb - $SUB1_GR_MK;
                        } else {
                            $splgr_elgb   =  0;
                            $SUB1_GR_MK  = "";
                        }
                    }
                }
                //echo $SUB1_GR_MK . '  1 =>';

                if (($row["SUB2_APPR_YR"] == $year) && ($is_s1_abs == 0)) {
                    if (!is_null($SUB2_PASS_MK) && ($row["SUB2_OB_MK"] < $SUB2_PASS_MK->paper_pass_marks)) {
                        if (($SUB2_PASS_MK->paper_pass_marks - $row["SUB2_OB_MK"]) <= $gr_avb) {
                            $SUB2_GR_MK   =  $SUB2_PASS_MK->paper_pass_marks - $row["SUB2_OB_MK"];
                        } else {
                            $splgr_elgb   =  0;
                            $SUB2_GR_MK  = "";
                        }
                    }
                }

                //echo $SUB2_GR_MK . '  2 =>';

                if (($row["SUB3_APPR_YR"] == $year) && ($is_s1_abs == 0)) {
                    if (!is_null($SUB3_PASS_MK) && ($row["SUB3_OB_MK"] < $SUB3_PASS_MK->paper_pass_marks)) {
                        if (($SUB3_PASS_MK->paper_pass_marks - $row["SUB3_OB_MK"]) <= $gr_avb) {
                            $SUB3_GR_MK   =  $SUB3_PASS_MK->paper_pass_marks - $row["SUB3_OB_MK"];
                        } else {
                            $splgr_elgb   =  0;
                            $SUB3_GR_MK  = "";
                        }
                    }
                }

                //echo $SUB3_GR_MK . '  3 =>';

                if (($row["SUB4_APPR_YR"] == $year) && ($is_s1_abs == 0)) {
                    if (!is_null($SUB4_PASS_MK) && ($row["SUB4_OB_MK"] < $SUB4_PASS_MK->paper_pass_marks)) {
                        if (($SUB4_PASS_MK->paper_pass_marks - $row["SUB4_OB_MK"]) <= $gr_avb) {
                            $SUB4_GR_MK   =  $SUB4_PASS_MK->paper_pass_marks - $row["SUB4_OB_MK"];
                        } else {
                            $splgr_elgb   =  0;
                            $SUB4_GR_MK  = "";
                        }
                    }
                }

                //echo $SUB4_GR_MK . '  4 =>';

                if (($row["SUB5_APPR_YR"] == $year) && ($is_s1_abs == 0)) {
                    if (!is_null($SUB5_PASS_MK) && ($row["SUB5_OB_MK"] < $SUB5_PASS_MK->paper_pass_marks)) {
                        if (($SUB5_PASS_MK->paper_pass_marks - $row["SUB5_OB_MK"]) <= $gr_avb) {
                            $SUB5_GR_MK   =  $SUB5_PASS_MK->paper_pass_marks - $row["SUB5_OB_MK"];
                        } else {
                            $splgr_elgb   =  0;
                            $SUB5_GR_MK  = "";
                        }
                    }
                }

                //echo $SUB5_GR_MK . '  5 =>';

                $credit = "";
                if (($is_abs == 0) && $SUB1_GR_MK == 1) {
                    $SUB1_GR   =  (int)$SUB1_GR_MK;
                    $SUB1_nw_mk = $SUB1_GR + $SUB1_OB_MK;
                    if (!is_null($SUB1_PASS_MK) && ($SUB1_nw_mk >= $SUB1_PASS_MK->paper_pass_marks)) {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB1_SPL_GR' => $SUB1_GR,
                                'SUB1_OB_MK' => (int)$SUB1_nw_mk,
                                'SUB1_TOT_RES' => 'Q',
                            ]);
                        $credit .= "SUB1,";
                    }
                }
                if (($is_abs == 0) && $SUB2_GR_MK == 1) {
                    $SUB2_GR   =  (int)$SUB2_GR_MK;
                    $SUB2_nw_mk = $SUB2_GR + $SUB2_OB_MK;
                    if (!is_null($SUB2_PASS_MK) && ($SUB2_nw_mk >= $SUB2_PASS_MK->paper_pass_marks)) {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB2_SPL_GR' => $SUB2_GR,
                                'SUB2_OB_MK' => (int)$SUB2_nw_mk,
                                'SUB2_TOT_RES' => 'Q',
                            ]);
                        $credit .= "SUB2,";
                    }
                }
                if (($is_abs == 0) && $SUB3_GR_MK == 1) {
                    $SUB3_GR   =  (int)$SUB3_GR_MK;
                    $SUB3_nw_mk = $SUB3_GR + $SUB3_OB_MK;
                    if (!is_null($SUB3_PASS_MK) && ($SUB3_nw_mk >= $SUB3_PASS_MK->paper_pass_marks)) {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB3_SPL_GR' => $SUB3_GR,
                                'SUB3_OB_MK' => (int)$SUB3_nw_mk,
                                'SUB3_TOT_RES' => 'Q',
                            ]);
                        $credit .= "SUB3,";
                    }
                }
                if (($is_abs == 0) && $SUB4_GR_MK == 1) {
                    $SUB4_GR   =  (int)$SUB4_GR_MK;
                    $SUB4_nw_mk = $SUB4_GR + $SUB4_OB_MK;
                    if (!is_null($SUB4_PASS_MK) && ($SUB4_nw_mk >= $SUB4_PASS_MK->paper_pass_marks)) {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB4_SPL_GR' => $SUB4_GR,
                                'SUB4_OB_MK' => (int)$SUB4_nw_mk,
                                'SUB4_TOT_RES' => 'Q',
                            ]);
                        $credit .= "SUB4,";
                    }
                }
                if (($is_abs == 0) && $SUB5_GR_MK == 1) {
                    $SUB5_GR   =  (int)$SUB5_GR_MK;
                    $SUB5_nw_mk = $SUB5_GR + $SUB5_OB_MK;
                    if (!is_null($SUB5_PASS_MK) && ($SUB5_nw_mk >= $SUB5_PASS_MK->paper_pass_marks)) {
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'SUB5_SPL_GR' => $SUB5_GR,
                                'SUB5_OB_MK' => (int)$SUB5_nw_mk,
                                'SUB5_TOT_RES' => 'Q',
                            ]);
                        $credit .= "SUB5,";
                    }
                }


                $GT =   (int)$row["SUB1_OB_MK"] +
                    (int)$row["SUB2_OB_MK"] +
                    (int)$row["SUB3_OB_MK"] +
                    (int)$row["SUB4_OB_MK"] +
                    (int)$row["SUB5_OB_MK"] +
                    (int)$row["SUB6_OB_MK"] + (int)$row["SUB7_OB_MK"] +
                    (int)$row["SUB8_OB_MK"];

                //$credit = rtrim($credit, ",");

                Result::where('REG_NO', $row['REG_NO'])
                    ->update([
                        'GRAND_TOTAL' => $GT
                    ]);
            }
            echo 'Updated Successfully';
        } else {
            echo 'No data found';
        }
    }

    //Step 4 Grafting
    public function resultProcessScriptStep4(Request $request)
    {
        $year = $request->year;
        $resultData = Result::orderBy('SL', 'asc')->get();
        $paper = Paper::query();
        if (sizeof($resultData) > 0) {
            foreach ($resultData as $key => $row) {
                $passed_paper_marks = [];
                $failed_paper_marks = [];
                $gr_avb = 5;
                $SUB1_GR_MK = '';
                $SUB2_GR_MK = '';
                $SUB3_GR_MK = '';
                $SUB4_GR_MK = '';
                $SUB5_GR_MK = '';
                $SUB1_GR = '';
                $SUB2_GR = '';
                $SUB3_GR = '';
                $SUB4_GR = '';
                $SUB5_GR = '';
                //$P1_GR = ''; $P2_GR = ''; $P3_GR = ''; $P4_GR = ''; 
                $gr_elgb = 1;
                $GT = "";
                $is_abs = 0;
                $is_s1_abs = 0;
                $is_s2_abs = 0;
                $is_s3_abs = 0;
                $is_s4_abs = 0;
                $is_s5_abs = 0;
                $SUB1_PASS_MK = (!is_null($row['SUB1_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB1_ID']) : NULL;
                $SUB2_PASS_MK = (!is_null($row['SUB2_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB2_ID']) : NULL;
                $SUB3_PASS_MK = (!is_null($row['SUB3_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB3_ID']) : NULL;
                $SUB4_PASS_MK = (!is_null($row['SUB4_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB4_ID']) : NULL;
                $SUB5_PASS_MK = (!is_null($row['SUB5_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB5_ID']) : NULL;
                $SUB6_PASS_MK = (!is_null($row['SUB6_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB6_ID']) : NULL;
                $SUB7_PASS_MK = (!is_null($row['SUB7_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB7_ID']) : NULL;
                $SUB8_PASS_MK = (!is_null($row['SUB8_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB8_ID']) : NULL;


                $SUB1_OB_MK = (int)$row['SUB1_OB_MK'];
                $SUB2_OB_MK = (int)$row['SUB2_OB_MK'];
                $SUB3_OB_MK = (int)$row['SUB3_OB_MK'];
                $SUB4_OB_MK = (int)$row['SUB4_OB_MK'];
                $SUB5_OB_MK = (int)$row['SUB5_OB_MK'];
                $SUB6_OB_MK = (int)$row['SUB6_OB_MK'];
                $SUB7_OB_MK = (int)$row['SUB7_OB_MK'];
                $SUB8_OB_MK = (int)$row['SUB8_OB_MK'];

                $SUB6_INT_OB_MK = $row['SUB6_SES_INT_MK_' . $year . ''];
                $SUB6_EXT_OB_MK = $row['SUB6_SES_EXT_MK_' . $year . ''];

                $SUB7_INT_OB_MK = $row['SUB7_SES_INT_MK_' . $year . ''];
                $SUB7_EXT_OB_MK = $row['SUB7_SES_EXT_MK_' . $year . ''];

                $SUB8_INT_OB_MK = $row['SUB8_SES_INT_MK_' . $year . ''];
                $SUB8_EXT_OB_MK = $row['SUB8_SES_EXT_MK_' . $year . ''];
                $credit = "";

                $count = 0;
                //$credit = "";
                if ($SUB1_OB_MK > 0 && $SUB1_OB_MK < $SUB1_PASS_MK->paper_pass_marks && !is_null($SUB1_PASS_MK)) {
                    $count++;
                    $sub = 'SUB1';
                    $sub1shortmarks = $SUB1_PASS_MK->paper_pass_marks - $SUB1_OB_MK;
                    $sub1extramarks = $SUB1_OB_MK - $SUB1_PASS_MK->paper_pass_marks;
                    // echo $sub1extramarks;
                    // exit();
                    $failed_paper_marks = $failed_paper_marks + ['SUB1' => (int)$sub1shortmarks];
                    //$passed_paper_marks = $passed_paper_marks + ['SUB1' =>  $SUB1_OB_MK];
                }
                if ($SUB2_OB_MK > 0 && $SUB2_OB_MK < $SUB2_PASS_MK->paper_pass_marks && !is_null($SUB2_PASS_MK)) {
                    $count++;
                    $sub = 'SUB2';
                    $sub2shortmarks = $SUB2_PASS_MK->paper_pass_marks - $SUB2_OB_MK;
                    $sub2extramarks = $SUB2_OB_MK - $SUB2_PASS_MK->paper_pass_marks;
                    $failed_paper_marks = $failed_paper_marks + ['SUB2' => (int)$sub2shortmarks];
                    //$passed_paper_marks = $passed_paper_marks + ['SUB2' =>  $SUB2_OB_MK];
                }
                if ($SUB3_OB_MK > 0 && $SUB3_OB_MK < $SUB3_PASS_MK->paper_pass_marks && !is_null($SUB3_PASS_MK)) {
                    $count++;
                    $sub = 'SUB3';
                    $sub3shortmarks = $SUB3_PASS_MK->paper_pass_marks - $SUB3_OB_MK;
                    $sub3extramarks = $SUB3_OB_MK - $SUB3_PASS_MK->paper_pass_marks;
                    $failed_paper_marks = $failed_paper_marks + ['SUB3' => $sub3shortmarks];
                    //$passed_paper_marks = $passed_paper_marks + ['SUB3' =>  $SUB3_OB_MK];
                }
                if ($SUB4_OB_MK > 0 && $SUB4_OB_MK < $SUB4_PASS_MK->paper_pass_marks && !is_null($SUB4_PASS_MK)) {
                    $count++;
                    $sub = 'SUB4';
                    $sub4shortmarks = $SUB4_PASS_MK->paper_pass_marks - $SUB4_OB_MK;
                    $sub4extramarks = $SUB4_OB_MK - $SUB4_PASS_MK->paper_pass_marks;
                    $failed_paper_marks = $failed_paper_marks + ['SUB4' => (int)$sub4shortmarks];
                    //$passed_paper_marks = $passed_paper_marks + ['SUB4' =>  $SUB4_OB_MK];
                }
                if ($SUB5_OB_MK > 0 && $SUB5_OB_MK < $SUB5_PASS_MK->paper_pass_marks && !is_null($SUB5_PASS_MK)) {
                    $count++;
                    $sub = 'SUB5';
                    $sub5shortmarks = $SUB5_PASS_MK->paper_pass_marks - $SUB5_OB_MK;
                    $sub5extramarks = $SUB5_OB_MK - $SUB5_PASS_MK->paper_pass_marks;
                    $failed_paper_marks = $failed_paper_marks + ['SUB5' => (int)$sub5shortmarks];
                    //$passed_paper_marks = $passed_paper_marks + ['SUB5' =>  $SUB5_OB_MK];
                }

                if ($count == 1) {
                    if ($SUB1_OB_MK > 0 && $SUB1_OB_MK > $SUB1_PASS_MK->paper_pass_marks && !is_null($SUB1_PASS_MK)) {
                        $passed_paper_marks = $passed_paper_marks + ['SUB1' =>  $SUB1_OB_MK];
                    }
                    if ($SUB2_OB_MK > 0 && $SUB2_OB_MK > $SUB2_PASS_MK->paper_pass_marks && !is_null($SUB2_PASS_MK)) {
                        $passed_paper_marks = $passed_paper_marks + ['SUB2' =>  $SUB2_OB_MK];
                    }
                    if ($SUB3_OB_MK > 0 && $SUB3_OB_MK > $SUB3_PASS_MK->paper_pass_marks && !is_null($SUB3_PASS_MK)) {
                        $passed_paper_marks = $passed_paper_marks + ['SUB3' =>  $SUB3_OB_MK];
                    }
                    if ($SUB4_OB_MK > 0 && $SUB4_OB_MK > $SUB4_PASS_MK->paper_pass_marks && !is_null($SUB4_PASS_MK)) {
                        $passed_paper_marks = $passed_paper_marks + ['SUB4' =>  $SUB4_OB_MK];
                    }
                    if ($SUB5_OB_MK > 0 && $SUB5_OB_MK > $SUB5_PASS_MK->paper_pass_marks && !is_null($SUB5_PASS_MK)) {
                        $passed_paper_marks = $passed_paper_marks + ['SUB5' =>  $SUB5_OB_MK];
                    }
                    // echo '<pre>';
                    // var_dump($passed_paper_marks);
                    if (count($passed_paper_marks) > 0 && count($failed_paper_marks) > 0) {

                        arsort($passed_paper_marks);
                        arsort($failed_paper_marks);
                        $best_paper_keys = array_keys($passed_paper_marks);
                        $fail_paper_keys = array_keys($failed_paper_marks);

                        $highestMarks = $passed_paper_marks[$best_paper_keys[0]];
                        $shortMarkstoPass = $failed_paper_marks[$fail_paper_keys[0]];
                        $subpassmarksId = $row['' . $best_paper_keys[0] . '_ID'];
                        $check = $this->calculateGrafting($highestMarks, $shortMarkstoPass, $subpassmarksId);
                        if ($check) {
                            //$old_fail_sub =$fail_paper_keys[0] . '
                            $grafting_to_key_name_obtain_column = $fail_paper_keys[0] . '_OB_MK';
                            $grafting_to_result_key_name = $fail_paper_keys[0] . '_TOT_RES';
                            $grafting_to_grace_key_name = $fail_paper_keys[0] . '_GR_MK';
                            $grafting_from_grace_key_name = $best_paper_keys[0] . '_GR_MK';
                            $grafting_from_key_name_obtain_column = $best_paper_keys[0] . '_OB_MK';
                            $grafting_to_sub_obtain_marks = $row['' . $fail_paper_keys[0] . '_OB_MK'];
                            $grafting_to_sub_new_obtain_marks = $grafting_to_sub_obtain_marks + (int)$shortMarkstoPass;
                            $grafting_from_pass_sub_new_obtain_marks = $highestMarks - (int)$shortMarkstoPass;
                            // echo $grafting_to_key_name_obtain_column . '---to name';
                            // echo $grafting_to_sub_obtain_marks . '-----obtain marks';
                            // echo $grafting_from_key_name_obtain_column . '-----from';
                            // echo $grafting_to_sub_new_obtain_marks . '-----new marks';
                            // echo $grafting_from_pass_sub_new_obtain_marks . '-----after grafting from sub new marks';
                            Result::where('REG_NO', $row['REG_NO'])
                                ->update([
                                    '' . $grafting_to_grace_key_name . '' => '+' . (int)$shortMarkstoPass,
                                    '' . $grafting_from_grace_key_name . '' => '-' . (int)$shortMarkstoPass,
                                    '' . $grafting_from_key_name_obtain_column . '' => $grafting_from_pass_sub_new_obtain_marks,
                                    '' . $grafting_to_key_name_obtain_column . '' => $grafting_to_sub_new_obtain_marks,
                                    '' . $grafting_to_result_key_name . '' => 'Q'
                                ]);
                        }
                        // echo 'hi';
                    }
                }
            }
            echo 'Updated Successfully';
        } else {
            echo 'No data found';
        }
    }

    //Step 5 After grafting grand total & credit update
    public function resultProcessScriptStep5(Request $request)
    {
        $year = $request->year;
        $resultData = Result::orderBy('SL', 'asc')->get();
        $paper = Paper::query();

        if (sizeof($resultData) > 0) {
            foreach ($resultData as $key => $row) {
                $GT = "";
                $is_abs = 0;
                $is_s1_abs = 0;
                $is_s2_abs = 0;
                $is_s3_abs = 0;
                $is_s4_abs = 0;
                $is_s5_abs = 0;
                $is_s6_abs = 0;
                $is_s7_abs = 0;
                $is_s8_abs = 0;
                $SUB1_PASS_MK = (!is_null($row['SUB1_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB1_ID']) : NULL;
                $SUB2_PASS_MK = (!is_null($row['SUB2_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB2_ID']) : NULL;
                $SUB3_PASS_MK = (!is_null($row['SUB3_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB3_ID']) : NULL;
                $SUB4_PASS_MK = (!is_null($row['SUB4_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB4_ID']) : NULL;
                $SUB5_PASS_MK = (!is_null($row['SUB5_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB5_ID']) : NULL;
                $SUB6_PASS_MK = (!is_null($row['SUB6_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB6_ID']) : NULL;
                $SUB7_PASS_MK = (!is_null($row['SUB7_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB7_ID']) : NULL;
                $SUB8_PASS_MK = (!is_null($row['SUB8_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB8_ID']) : NULL;


                $SUB1_OB_MK = (int)$row['SUB1_OB_MK'];
                $SUB2_OB_MK = (int)$row['SUB2_OB_MK'];
                $SUB3_OB_MK = (int)$row['SUB3_OB_MK'];
                $SUB4_OB_MK = (int)$row['SUB4_OB_MK'];
                $SUB5_OB_MK = (int)$row['SUB5_OB_MK'];
                $SUB6_OB_MK = (int)$row['SUB6_OB_MK'];
                $SUB7_OB_MK = (int)$row['SUB7_OB_MK'];
                $SUB8_OB_MK = (int)$row['SUB8_OB_MK'];

                $SUB6_INT_OB_MK = $row['SUB6_SES_INT_MK_' . $year . ''];
                $SUB6_EXT_OB_MK = $row['SUB6_SES_EXT_MK_' . $year . ''];

                $SUB7_INT_OB_MK = $row['SUB7_SES_INT_MK_' . $year . ''];
                $SUB7_EXT_OB_MK = $row['SUB7_SES_EXT_MK_' . $year . ''];

                $SUB8_INT_OB_MK = $row['SUB8_SES_INT_MK_' . $year . ''];
                $SUB8_EXT_OB_MK = $row['SUB8_SES_EXT_MK_' . $year . ''];
                $credit = "";

                if (
                    ($row["SUB1_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB1_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB1_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s1_abs = 1;
                }

                if (
                    ($row["SUB2_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB2_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB2_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s2_abs = 1;
                }

                if (
                    ($row["SUB3_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB3_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB3_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s3_abs = 1;
                }

                if (
                    ($row["SUB4_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB4_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB4_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s4_abs = 1;
                }

                if (
                    ($row["SUB5_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB5_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB5_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s5_abs = 1;
                }

                if (
                    ($row["SUB6_SES_INT_MK_" . $year] == "AB") ||
                    ($row["SUB6_SES_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s6_abs = 1;
                }
                if (
                    ($row["SUB7_SES_INT_MK_" . $year] == "AB") ||
                    ($row["SUB7_SES_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s7_abs = 1;
                }
                if (
                    ($row["SUB8_SES_INT_MK_" . $year] == "AB") ||
                    ($row["SUB8_SES_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s8_abs = 1;
                }

                if ($row["SUB1_APPR_YR"] == $year) {
                    if (!is_null($SUB1_PASS_MK) && ($row["SUB1_OB_MK"] >= $SUB1_PASS_MK->paper_pass_marks) && ($is_s1_abs == 0)) {
                        $credit .= "SUB1,";
                    }
                }
                if ($row["SUB2_APPR_YR"] == $year) {
                    if (!is_null($SUB2_PASS_MK) && ($row["SUB2_OB_MK"] >= $SUB2_PASS_MK->paper_pass_marks) && ($is_s2_abs == 0)) {
                        $credit .= "SUB2,";
                    }
                }
                if ($row["SUB3_APPR_YR"] == $year) {
                    if (!is_null($SUB3_PASS_MK) && ($row["SUB3_OB_MK"] >= $SUB3_PASS_MK->paper_pass_marks) && ($is_s3_abs == 0)) {
                        $credit .= "SUB3,";
                    }
                }
                if ($row["SUB4_APPR_YR"] == $year) {
                    if (!is_null($SUB4_PASS_MK) && ($row["SUB4_OB_MK"] >= $SUB4_PASS_MK->paper_pass_marks) && ($is_s4_abs == 0)) {
                        $credit .= "SUB4,";
                    }
                }
                if ($row["SUB5_APPR_YR"] == $year) {
                    if (!is_null($SUB5_PASS_MK) && ($row["SUB5_OB_MK"] >= $SUB5_PASS_MK->paper_pass_marks) && ($is_s5_abs == 0)) {
                        $credit .= "SUB5,";
                    }
                }
                if ($row["SUB6_APPR_YR"] == $year) {
                    if (!is_null($SUB6_PASS_MK) && ($row["SUB6_OB_MK"] >= $SUB6_PASS_MK->paper_pass_marks) && ($is_s6_abs == 0)) {
                        $credit .= "SUB6,";
                    }
                }
                if ($row["SUB7_APPR_YR"] == $year) {
                    if (!is_null($SUB7_PASS_MK) && ($row["SUB7_OB_MK"] >= $SUB7_PASS_MK->paper_pass_marks) && ($is_s7_abs == 0)) {
                        $credit .= "SUB7,";
                    }
                }
                if ($row["SUB8_APPR_YR"] == $year) {
                    if (!is_null($SUB8_PASS_MK) && ($row["SUB8_OB_MK"] >= $SUB8_PASS_MK->paper_pass_marks) && ($is_s8_abs == 0)) {
                        $credit .= "SUB8,";
                    }
                }
                $GT =   (int)$row["SUB1_OB_MK"] +
                    (int)$row["SUB2_OB_MK"] +
                    (int)$row["SUB3_OB_MK"] +
                    (int)$row["SUB4_OB_MK"] +
                    (int)$row["SUB5_OB_MK"] +
                    (int)$row["SUB6_OB_MK"] + (int)$row["SUB7_OB_MK"] +
                    (int)$row["SUB8_OB_MK"];
                Result::where('REG_NO', $row['REG_NO'])
                    ->update([
                        'GRAND_TOTAL' => $GT,
                        'CREDIT_' . $year . '' => $credit
                    ]);
            }
            echo 'Updated Successfully';
        } else {
            echo 'No Data Found';
        }
    }

    //Step 6 calculate fail if only fail in one sessional paper
    public function resultProcessScriptStep6(Request $request)
    {
        $year = $request->year;
        $resultData = Result::orderBy('SL', 'asc')->get();
        $paper = Paper::query();
        if (sizeof($resultData) > 0) {
            foreach ($resultData as $key => $row) {
                $failed_paper_marks = [];
                $GT = "";
                $is_abs = 0;
                $is_s1_abs = 0;
                $is_s2_abs = 0;
                $is_s3_abs = 0;
                $is_s4_abs = 0;
                $is_s5_abs = 0;
                $is_s6_abs = 0;
                $is_s7_abs = 0;
                $is_s8_abs = 0;
                $SUB1_PASS_MK = (!is_null($row['SUB1_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB1_ID']) : NULL;
                $SUB2_PASS_MK = (!is_null($row['SUB2_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB2_ID']) : NULL;
                $SUB3_PASS_MK = (!is_null($row['SUB3_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB3_ID']) : NULL;
                $SUB4_PASS_MK = (!is_null($row['SUB4_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB4_ID']) : NULL;
                $SUB5_PASS_MK = (!is_null($row['SUB5_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB5_ID']) : NULL;
                $SUB6_PASS_MK = (!is_null($row['SUB6_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB6_ID']) : NULL;
                $SUB7_PASS_MK = (!is_null($row['SUB7_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB7_ID']) : NULL;
                $SUB8_PASS_MK = (!is_null($row['SUB8_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB8_ID']) : NULL;
                //dd($SUB7_PASS_MK);
                $SUB1_OB_MK = (int)$row['SUB1_OB_MK'];
                $SUB2_OB_MK = (int)$row['SUB2_OB_MK'];
                $SUB3_OB_MK = (int)$row['SUB3_OB_MK'];
                $SUB4_OB_MK = (int)$row['SUB4_OB_MK'];
                $SUB5_OB_MK = (int)$row['SUB5_OB_MK'];
                $SUB6_OB_MK = (int)$row['SUB6_OB_MK'];
                $SUB7_OB_MK = (int)$row['SUB7_OB_MK'];
                $SUB8_OB_MK = (int)$row['SUB8_OB_MK'];

                $SUB6_INT_OB_MK = $row['SUB6_SES_INT_MK_' . $year . ''];
                $SUB6_EXT_OB_MK = $row['SUB6_SES_EXT_MK_' . $year . ''];

                $SUB7_INT_OB_MK = $row['SUB7_SES_INT_MK_' . $year . ''];
                $SUB7_EXT_OB_MK = $row['SUB7_SES_EXT_MK_' . $year . ''];

                $SUB8_INT_OB_MK = $row['SUB8_SES_INT_MK_' . $year . ''];
                $SUB8_EXT_OB_MK = $row['SUB8_SES_EXT_MK_' . $year . ''];
                $credit = "";

                $count = 0;
                $credit = "";
                if ($SUB6_OB_MK > 0 && $SUB6_OB_MK < $SUB6_PASS_MK->paper_pass_marks && !is_null($SUB6_PASS_MK)) {
                    $count++;
                    $sub6shortmarks = $SUB6_PASS_MK->paper_pass_marks - $SUB6_OB_MK;
                    $sub6extramarks = $SUB6_OB_MK - $SUB6_PASS_MK->paper_pass_marks;
                    $failed_paper_marks = $failed_paper_marks + ['SUB6' => (int)$sub6shortmarks];
                }
                if ($SUB7_OB_MK > 0 && $SUB7_OB_MK < $SUB7_PASS_MK->paper_pass_marks && !is_null($SUB7_PASS_MK)) {
                    $count++;
                    $sub7shortmarks = $SUB7_PASS_MK->paper_pass_marks - $SUB7_OB_MK;
                    $sub7extramarks = $SUB7_OB_MK - $SUB7_PASS_MK->paper_pass_marks;
                    $failed_paper_marks = $failed_paper_marks + ['SUB7' => (int)$sub7shortmarks];
                }
                if ($SUB8_OB_MK > 0 && $SUB8_OB_MK < $SUB8_PASS_MK->paper_pass_marks && !is_null($SUB8_PASS_MK)) {
                    $count++;
                    $sub8shortmarks = $SUB8_PASS_MK->paper_pass_marks - $SUB8_OB_MK;
                    $sub8extramarks = $SUB8_OB_MK - $SUB8_PASS_MK->paper_pass_marks;
                    $failed_paper_marks = $failed_paper_marks + ['SUB8' => (int)$sub8shortmarks];
                }

                if (
                    ($row["SUB1_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB1_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB1_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s1_abs = 1;
                }

                if (
                    ($row["SUB2_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB2_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB2_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s2_abs = 1;
                }

                if (
                    ($row["SUB3_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB3_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB3_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s3_abs = 1;
                }

                if (
                    ($row["SUB4_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB4_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB4_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s4_abs = 1;
                }

                if (
                    ($row["SUB5_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB5_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB5_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s5_abs = 1;
                }

                if (
                    ($row["SUB6_SES_INT_MK_" . $year] == "AB") ||
                    ($row["SUB6_SES_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s6_abs = 1;
                }
                if (
                    ($row["SUB7_SES_INT_MK_" . $year] == "AB") ||
                    ($row["SUB7_SES_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s7_abs = 1;
                }
                if (
                    ($row["SUB8_SES_INT_MK_" . $year] == "AB") ||
                    ($row["SUB8_SES_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s8_abs = 1;
                }

                if ($count = 1) {
                    if ($row["SUB1_APPR_YR"] == $year) {
                        if (!is_null($SUB1_PASS_MK) && ($row["SUB1_OB_MK"] >= $SUB1_PASS_MK->paper_pass_marks) && ($is_s1_abs == 0)) {
                            $credit .= "SUB1,";
                        }
                    }
                    if ($row["SUB2_APPR_YR"] == $year) {
                        if (!is_null($SUB2_PASS_MK) && ($row["SUB2_OB_MK"] >= $SUB2_PASS_MK->paper_pass_marks) && ($is_s2_abs == 0)) {
                            $credit .= "SUB2,";
                        }
                    }
                    if ($row["SUB3_APPR_YR"] == $year) {
                        if (!is_null($SUB3_PASS_MK) && ($row["SUB3_OB_MK"] >= $SUB3_PASS_MK->paper_pass_marks) && ($is_s3_abs == 0)) {
                            $credit .= "SUB3,";
                        }
                    }
                    if ($row["SUB4_APPR_YR"] == $year) {
                        if (!is_null($SUB4_PASS_MK) && ($row["SUB4_OB_MK"] >= $SUB4_PASS_MK->paper_pass_marks) && ($is_s4_abs == 0)) {
                            $credit .= "SUB4,";
                        }
                    }
                    if ($row["SUB5_APPR_YR"] == $year) {
                        if (!is_null($SUB5_PASS_MK) && ($row["SUB5_OB_MK"] >= $SUB5_PASS_MK->paper_pass_marks) && ($is_s5_abs == 0)) {
                            $credit .= "SUB5,";
                        }
                    }
                    if ($row["SUB6_APPR_YR"] == $year) {
                        if (!is_null($SUB6_PASS_MK) && ($row["SUB6_OB_MK"] >= $SUB6_PASS_MK->paper_pass_marks) && ($is_s6_abs == 0)) {
                            $credit .= "SUB6,";
                        }
                    }
                    if ($row["SUB7_APPR_YR"] == $year) {
                        if (!is_null($SUB7_PASS_MK) && ($row["SUB7_OB_MK"] >= $SUB7_PASS_MK->paper_pass_marks) && ($is_s7_abs == 0)) {
                            $credit .= "SUB7,";
                        }
                    }
                    if ($row["SUB8_APPR_YR"] == $year) {
                        if (!is_null($SUB8_PASS_MK) && ($row["SUB8_OB_MK"] >= $SUB8_PASS_MK->paper_pass_marks) && ($is_s8_abs == 0)) {
                            $credit .= "SUB8,";
                        }
                    }

                    if (count($failed_paper_marks) > 0) {

                        arsort($failed_paper_marks);
                        $fail_paper_keys = array_keys($failed_paper_marks);

                        $fail_column = $fail_paper_keys[0] . '_TOT_RES';

                        $GT =   (int)$row["SUB1_OB_MK"] +
                            (int)$row["SUB2_OB_MK"] +
                            (int)$row["SUB3_OB_MK"] +
                            (int)$row["SUB4_OB_MK"] +
                            (int)$row["SUB5_OB_MK"] +
                            (int)$row["SUB6_OB_MK"] + (int)$row["SUB7_OB_MK"] +
                            (int)$row["SUB8_OB_MK"];
                        Result::where('REG_NO', $row['REG_NO'])
                            ->update([
                                'GRAND_TOTAL' => $GT,
                                'CREDIT_' . $year . '' => $credit,
                                'RESULT' => 'X',
                                '' . $fail_column . '' => 'X',
                            ]);
                    }
                }
            }
        }
    }

    //Step 7 calculate back if fail in theory 2 subjects and calculate fail if fail in morethan 2 theory subjects
    public function resultProcessScriptStep7(Request $request)
    {
        $year = $request->year;
        $resultData = Result::orderBy('SL', 'asc')->get();
        $paper = Paper::query();
        if (sizeof($resultData) > 0) {
            foreach ($resultData as $key => $row) {
                $GT = "";
                $is_abs = 0;
                $is_s1_abs = 0;
                $is_s2_abs = 0;
                $is_s3_abs = 0;
                $is_s4_abs = 0;
                $is_s5_abs = 0;
                $is_s6_abs = 0;
                $is_s7_abs = 0;
                $is_s8_abs = 0;
                $SUB1_PASS_MK = (!is_null($row['SUB1_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB1_ID']) : NULL;
                $SUB2_PASS_MK = (!is_null($row['SUB2_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB2_ID']) : NULL;
                $SUB3_PASS_MK = (!is_null($row['SUB3_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB3_ID']) : NULL;
                $SUB4_PASS_MK = (!is_null($row['SUB4_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB4_ID']) : NULL;
                $SUB5_PASS_MK = (!is_null($row['SUB5_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB5_ID']) : NULL;
                $SUB6_PASS_MK = (!is_null($row['SUB6_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB6_ID']) : NULL;
                $SUB7_PASS_MK = (!is_null($row['SUB7_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB7_ID']) : NULL;
                $SUB8_PASS_MK = (!is_null($row['SUB8_ID'])) ? $paper->clone()->select('paper_pass_marks')->find($row['SUB8_ID']) : NULL;
                //dd($SUB7_PASS_MK);
                $SUB1_OB_MK = (int)$row['SUB1_OB_MK'];
                $SUB2_OB_MK = (int)$row['SUB2_OB_MK'];
                $SUB3_OB_MK = (int)$row['SUB3_OB_MK'];
                $SUB4_OB_MK = (int)$row['SUB4_OB_MK'];
                $SUB5_OB_MK = (int)$row['SUB5_OB_MK'];
                $SUB6_OB_MK = (int)$row['SUB6_OB_MK'];
                $SUB7_OB_MK = (int)$row['SUB7_OB_MK'];
                $SUB8_OB_MK = (int)$row['SUB8_OB_MK'];

                $SUB6_INT_OB_MK = $row['SUB6_SES_INT_MK_' . $year . ''];
                $SUB6_EXT_OB_MK = $row['SUB6_SES_EXT_MK_' . $year . ''];

                $SUB7_INT_OB_MK = $row['SUB7_SES_INT_MK_' . $year . ''];
                $SUB7_EXT_OB_MK = $row['SUB7_SES_EXT_MK_' . $year . ''];

                $SUB8_INT_OB_MK = $row['SUB8_SES_INT_MK_' . $year . ''];
                $SUB8_EXT_OB_MK = $row['SUB8_SES_EXT_MK_' . $year . ''];

                $SUB1_TOT_RES = $row['SUB1_TOT_RES'];
                $credit = "";

                $count = 0;
                $credit = "";
                if ($SUB1_OB_MK > 0 && $SUB1_OB_MK < $SUB1_PASS_MK->paper_pass_marks && !is_null($SUB1_PASS_MK)) {
                    $count++;
                }
                if ($SUB2_OB_MK > 0 && $SUB2_OB_MK < $SUB2_PASS_MK->paper_pass_marks && !is_null($SUB2_PASS_MK)) {
                    $count++;
                }
                if ($SUB3_OB_MK > 0 && $SUB3_OB_MK < $SUB3_PASS_MK->paper_pass_marks && !is_null($SUB3_PASS_MK)) {
                    $count++;
                }
                if ($SUB4_OB_MK > 0 && $SUB4_OB_MK < $SUB4_PASS_MK->paper_pass_marks && !is_null($SUB4_PASS_MK)) {
                    $count++;
                }
                if ($SUB5_OB_MK > 0 && $SUB5_OB_MK < $SUB5_PASS_MK->paper_pass_marks && !is_null($SUB5_PASS_MK)) {
                    $count++;
                }
                if ($SUB6_OB_MK > 0 && $SUB6_OB_MK < $SUB6_PASS_MK->paper_pass_marks && !is_null($SUB6_PASS_MK)) {
                    $count++;
                }
                if ($SUB7_OB_MK > 0 && $SUB7_OB_MK < $SUB7_PASS_MK->paper_pass_marks && !is_null($SUB7_PASS_MK)) {
                    $count++;
                }
                if ($SUB8_OB_MK > 0 && $SUB8_OB_MK < $SUB8_PASS_MK->paper_pass_marks && !is_null($SUB8_PASS_MK)) {
                    $count++;
                }

                if (
                    ($row["SUB1_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB1_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB1_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s1_abs = 1;
                }

                if (
                    ($row["SUB2_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB2_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB2_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s2_abs = 1;
                }

                if (
                    ($row["SUB3_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB3_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB3_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s3_abs = 1;
                }

                if (
                    ($row["SUB4_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB4_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB4_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s4_abs = 1;
                }

                if (
                    ($row["SUB5_TH_INT_MK_" . $year] == "AB") ||
                    ($row["SUB5_TH_INT_ATD_MK_" . $year] == "AB")  ||
                    ($row["SUB5_TH_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s5_abs = 1;
                }
                if (
                    ($row["SUB6_SES_INT_MK_" . $year] == "AB") ||
                    ($row["SUB6_SES_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s6_abs = 1;
                }
                if (
                    ($row["SUB7_SES_INT_MK_" . $year] == "AB") ||
                    ($row["SUB7_SES_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s7_abs = 1;
                }
                if (
                    ($row["SUB8_SES_INT_MK_" . $year] == "AB") ||
                    ($row["SUB8_SES_EXT_MK_" . $year] == "AB")
                ) {
                    $is_s8_abs = 1;
                }



                if ($count > 1 && $count <=  2) { //Back
                    $credit = "";
                    if ($row["SUB1_APPR_YR"] == $year) {
                        if (!is_null($SUB1_PASS_MK) && ($row["SUB1_OB_MK"] >= $SUB1_PASS_MK->paper_pass_marks) && ($is_s1_abs == 0)) {
                            $credit .= "SUB1,";
                        }
                    }
                    if ($row["SUB2_APPR_YR"] == $year) {
                        if (!is_null($SUB2_PASS_MK) && ($row["SUB2_OB_MK"] >= $SUB2_PASS_MK->paper_pass_marks) && ($is_s2_abs == 0)) {
                            $credit .= "SUB2,";
                        }
                    }
                    if ($row["SUB3_APPR_YR"] == $year) {
                        if (!is_null($SUB3_PASS_MK) && ($row["SUB3_OB_MK"] >= $SUB3_PASS_MK->paper_pass_marks) && ($is_s3_abs == 0)) {
                            $credit .= "SUB3,";
                        }
                    }
                    if ($row["SUB4_APPR_YR"] == $year) {
                        if (!is_null($SUB4_PASS_MK) && ($row["SUB4_OB_MK"] >= $SUB4_PASS_MK->paper_pass_marks) && ($is_s4_abs == 0)) {
                            $credit .= "SUB4,";
                        }
                    }
                    if ($row["SUB5_APPR_YR"] == $year) {
                        if (!is_null($SUB5_PASS_MK) && ($row["SUB5_OB_MK"] >= $SUB5_PASS_MK->paper_pass_marks) && ($is_s5_abs == 0)) {
                            $credit .= "SUB5,";
                        }
                    }
                    if ($row["SUB6_APPR_YR"] == $year) {
                        if (!is_null($SUB6_PASS_MK) && ($row["SUB6_OB_MK"] >= $SUB6_PASS_MK->paper_pass_marks) && ($is_s6_abs == 0)) {
                            $credit .= "SUB6,";
                        }
                    }
                    if ($row["SUB7_APPR_YR"] == $year) {
                        if (!is_null($SUB7_PASS_MK) && ($row["SUB7_OB_MK"] >= $SUB7_PASS_MK->paper_pass_marks) && ($is_s7_abs == 0)) {
                            $credit .= "SUB7,";
                        }
                    }
                    if ($row["SUB8_APPR_YR"] == $year) {
                        if (!is_null($SUB8_PASS_MK) && ($row["SUB8_OB_MK"] >= $SUB8_PASS_MK->paper_pass_marks) && ($is_s8_abs == 0)) {
                            $credit .= "SUB8,";
                        }
                    }
                    $GT =   (int)$row["SUB1_OB_MK"] +
                        (int)$row["SUB2_OB_MK"] +
                        (int)$row["SUB3_OB_MK"] +
                        (int)$row["SUB4_OB_MK"] +
                        (int)$row["SUB5_OB_MK"] +
                        (int)$row["SUB6_OB_MK"] + (int)$row["SUB7_OB_MK"] +
                        (int)$row["SUB8_OB_MK"];
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            'GRAND_TOTAL' => $GT,
                            'RESULT' => 'B',
                            'CREDIT_' . $year . '' => $credit
                        ]);
                } else if ($count > 2) { //Fail
                    $credit = "";
                    if ($row["SUB1_APPR_YR"] == $year) {
                        if (!is_null($SUB1_PASS_MK) && ($row["SUB1_OB_MK"] >= $SUB1_PASS_MK->paper_pass_marks) && ($is_s1_abs == 0)) {
                            $credit .= "SUB1,";
                        }
                    }
                    if ($row["SUB2_APPR_YR"] == $year) {
                        if (!is_null($SUB2_PASS_MK) && ($row["SUB2_OB_MK"] >= $SUB2_PASS_MK->paper_pass_marks) && ($is_s2_abs == 0)) {
                            $credit .= "SUB2,";
                        }
                    }
                    if ($row["SUB3_APPR_YR"] == $year) {
                        if (!is_null($SUB3_PASS_MK) && ($row["SUB3_OB_MK"] >= $SUB3_PASS_MK->paper_pass_marks) && ($is_s3_abs == 0)) {
                            $credit .= "SUB3,";
                        }
                    }
                    if ($row["SUB4_APPR_YR"] == $year) {
                        if (!is_null($SUB4_PASS_MK) && ($row["SUB4_OB_MK"] >= $SUB4_PASS_MK->paper_pass_marks) && ($is_s4_abs == 0)) {
                            $credit .= "SUB4,";
                        }
                    }
                    if ($row["SUB5_APPR_YR"] == $year) {
                        if (!is_null($SUB5_PASS_MK) && ($row["SUB5_OB_MK"] >= $SUB5_PASS_MK->paper_pass_marks) && ($is_s5_abs == 0)) {
                            $credit .= "SUB5,";
                        }
                    }
                    if ($row["SUB6_APPR_YR"] == $year) {
                        if (!is_null($SUB6_PASS_MK) && ($row["SUB6_OB_MK"] >= $SUB6_PASS_MK->paper_pass_marks) && ($is_s6_abs == 0)) {
                            $credit .= "SUB6,";
                        }
                    }
                    if ($row["SUB7_APPR_YR"] == $year) {
                        if (!is_null($SUB7_PASS_MK) && ($row["SUB7_OB_MK"] >= $SUB7_PASS_MK->paper_pass_marks) && ($is_s7_abs == 0)) {
                            $credit .= "SUB7,";
                        }
                    }
                    if ($row["SUB8_APPR_YR"] == $year) {
                        if (!is_null($SUB8_PASS_MK) && ($row["SUB8_OB_MK"] >= $SUB8_PASS_MK->paper_pass_marks) && ($is_s8_abs == 0)) {
                            $credit .= "SUB8,";
                        }
                    }
                    $GT =   (int)$row["SUB1_OB_MK"] +
                        (int)$row["SUB2_OB_MK"] +
                        (int)$row["SUB3_OB_MK"] +
                        (int)$row["SUB4_OB_MK"] +
                        (int)$row["SUB5_OB_MK"] +
                        (int)$row["SUB6_OB_MK"] + (int)$row["SUB7_OB_MK"] +
                        (int)$row["SUB8_OB_MK"];
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            'GRAND_TOTAL' => $GT,
                            'RESULT' => 'X',
                            'CREDIT_' . $year . '' => $credit
                        ]);
                }
                if ($row['SUB1_TOT_RES'] == 'Q' && $row['SUB2_TOT_RES'] == 'Q' && $row['SUB3_TOT_RES'] == 'Q' && $row['SUB4_TOT_RES'] == 'Q' && $row['SUB5_TOT_RES'] == 'Q' && $row['SUB6_TOT_RES'] == 'Q' && $row['SUB7_TOT_RES'] == 'Q' && $row['SUB8_TOT_RES'] == 'Q') {
                    //ekhane subject er paper count r credit er count match kore ei update ta korte hobe
                    Result::where('REG_NO', $row['REG_NO'])
                        ->update([
                            //'GRAND_TOTAL' => $GT,
                            'RESULT' => 'Q',
                            //'CREDIT_' . $year . '' => $credit
                        ]);
                }
            }
            echo 'Updated successfully';
        } else {
            echo 'No data found';
        }
    }







    public function calculatePercentage($full_marks, $obtain_marks)
    {
        $percentage = ($obtain_marks * 100) / $full_marks;
        return $percentage;
    }

    public function calculateGrafting($marks, $short_marks, $subpassmarksId)
    {
        $SUB_PASS_MK = (!is_null($subpassmarksId)) ? Paper::select('paper_pass_marks')->find($subpassmarksId) : NULL;
        if ($SUB_PASS_MK) {
            $paper_pass_marks = (int)$SUB_PASS_MK->paper_pass_marks;
            if (($marks - $short_marks) > $paper_pass_marks) {
                return true;
            }
        }
    }
}
