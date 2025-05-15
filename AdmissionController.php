<?php

namespace App\Http\Controllers\wbscte;

use Exception;
use App\Models\wbscte\User;
use Illuminate\Support\Str;
use App\Models\wbscte\Token;
use App\Models\wbscte\Trade;
use Illuminate\Http\Request;
use App\Models\wbscte\Payment;
use Illuminate\Support\Carbon;
use App\Models\wbscte\District;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\wbscte\Institute;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\wbscte\JexpoApplElgbExam;
use Illuminate\Support\Facades\Validator;
use App\Models\wbscte\Schedule;
use App\Models\wbscte\Student;



class AdmissionController extends Controller
{
        public function submitStudents(Request $request)
    {
        {
            try {
                $validated = Validator::make($request->all(), [
                    'student_inst_id' => ['required'],
                    'student_first_name' => ['required'],
                    'student_middle_name' => ['nullable'],
                    'student_last_name' => ['required'],
                    'student_father_name' => ['required'],
                    'student_mother_name' => ['required'],
                    'student_guardian_name' => ['required'],
                    'student_dob' => ['required'],
                    'student_aadhar_no' => ['required', 'unique:jexpo_register_student,s_aadhar_original'],
                    'student_phone' => ['required', 'digits:10', 'unique:jexpo_register_student,s_phone'],
                    'student_email' => ['required', 'email', 'unique:jexpo_register_student,s_email'],
                    'student_gender' => ['required'],
                    'student_religion' => ['required'],
                    'student_caste' => ['required'],
                    'student_citizenship' => ['required'],
                    'student_subdivision' => ['required'],
                    // 'student_address2' => ['required'],
                    's_pwd' => ['required'],
                    'student_photo' => ['required'],
                    'student_sign' => ['required'],
                    'student_home_dist' => ['required'],
                    'student_state_id' => ['required'],
                    'student_address' => ['required'],
                    'student_pin_no' => ['required'],
                    'is_married' => ['required'],
                    'exam_total_marks' => ['required', 'numeric', 'min:1'],
                    'exam_board' => ['required'],
                    'exam_pass_yr' => ['required'],
                    'exam_result' => ['required'],
                    'obtained_marks' => ['required', 'numeric', 'min:0'],
                   
                ]);
                if ($validated->fails()) {
                    return response()->json([
                        'error' => true,
                        'message' => $validated->errors()->first()
                    ], 422);
                }
                $examTotalMarks = (int) $request->exam_total_marks;
                $obtainedMarks = (int) $request->obtained_marks;
                if ($obtainedMarks > $examTotalMarks) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Obtained marks cannot be greater than total marks.'
                    ], 400);
                }
    
                $currentDateTime = date('Y-m-d H:i:s');
                $schedule = Schedule::where('sch_event', 'APPLICATION')
                    ->where('sch_round', 1)
                    ->first();
                if (!$schedule || $currentDateTime < $schedule->sch_start_dt || $currentDateTime > $schedule->sch_end_dt) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your date is expired. You can no longer submit the form.'
                    ], 400);
                }
                $year = date('Y');
                $lastStudent = Student::latest('s_id')->first();
                if ($lastStudent && preg_match('/JEXPO' . $year . '(\d+)/', $lastStudent->s_appl_form_num, $matches)) {
                    $lastNumber = (int) $matches[1];
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }
                // Generate the new application form number
                $s_appl_form_num = 'JEXPO' . $year . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                $dob = new DateTime($request->student_dob);
                $currentYear = date('Y');
                $requiredDate = new DateTime("31-12-$currentYear");
                $age = $dob->diff($requiredDate)->y;
                if ($age < 19) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The candidate must be at least 17 years old on or before 31st December of this year.'
                    ], 400);
                }
    
                $fullAadhar = $request->student_aadhar_no;
                $enc_aadhaar_num      =   encryptHEXFormat($fullAadhar);
                $student_photo = null;
                if ($request->hasFile('student_photo')) {
                    $image = $request->file('student_photo');
                    $imageName = $s_appl_form_num . '_image.' . $image->getClientOriginalExtension();
                    $imagePath = 'uploads/' . $imageName;
                    $image->storeAs('uploads/', $imageName, 'public');
                    $student_photo = $imagePath;
                }
                $student_sign = null;
                if ($request->hasFile('student_sign')) {
                    $signature = $request->file('student_sign');
                    $signatureName = $s_appl_form_num . '_sign.' . $signature->getClientOriginalExtension();
                    $signaturePath = 'uploads/' . $signatureName;
                    $signature->storeAs('uploads/', $signatureName, 'public');
                    $student_sign = $signaturePath;
                }
    
                $uuid = Str::uuid()->toString();
                $instituteCode = Institute::where('i_id', $request->student_inst_id)->value('i_code');
                // dd($instituteCode);
                $register = Student::create([
                    's_inst_code' => $instituteCode,
                    's_appl_form_num' => $s_appl_form_num,
                    's_first_name' => trim($request->student_first_name),
                    's_middle_name' => trim($request->student_middle_name),
                    's_last_name' => trim($request->student_last_name),
                    's_candidate_name' => trim($request->student_first_name)
                        . ($request->student_middle_name ? ' ' . trim($request->student_middle_name) : '')
                        . ' ' . trim($request->student_last_name),
                    's_father_name' => trim($request->student_father_name),
                    's_mother_name' => trim($request->student_mother_name),
                    'student_guardian_name' => trim($request->student_guardian_name),
                    's_dob' => $request->student_dob,
                    's_aadhar_no' => $enc_aadhaar_num,
                    's_phone' => trim($request->student_phone),
                    's_email' => trim($request->student_email),
                    's_gender' => $request->student_gender,
                    's_religion' => $request->student_religion,
                    's_caste' => $request->student_caste,
                    'student_citizenship' => $request->student_citizenship,
                    'student_subdivision' => $request->student_subdivision,
                    'student_address2' => $request->student_address2,
                    's_pwd' => $request->s_pwd,
                    's_photo' => $student_photo,
                    's_sign' => $student_sign,
                    's_home_district' => trim($request->student_home_dist),
                    's_state_id' => $request->student_state_id,
                    'address' => trim($request->student_address),
                    'ps' => $request->student_police_station,
                    'po' => $request->student_post_office,
                    'pin' => trim($request->student_pin_no),
                    'is_married' => $request->is_married,
                    'student_kanyashree_no' => $request->student_kanyashree_no,
                    'is_kanyashree' => ($request->student_kanyashree_no && (strtolower($request->student_gender) == 'female' || strtoupper($request->student_gender) == 'FEMALE')) ? 1 : 0,
                    'is_profile_updated' => 1,
                    'is_active' => 1,
                    's_uuid' => $uuid,
                    's_admited_status'=>$request->s_admited_status,
                ]);
                
    
                if (!$register) {
                    return response()->json(['success' => false, 'message' => 'Failed to insert data.'], 500);
                }
    
                $eligibility = JexpoApplElgbExam::create([
                    'exam_appl_form_num' => $s_appl_form_num,
                    'exam_board' => $request->exam_board,
                    'exam_pass_yr' => $request->exam_pass_yr,
                    'exam_total_marks' => $request->exam_total_marks,
                    'obtained_marks' => $request->obtained_marks,
                    'exam_percentage' => $request->exam_percentage,
                    'exam_result' => $request->exam_result,
                    'exam_per_marks' => $request->exam_per_marks,
                    'exam_elgb_code'=>$request->exam_elgb_code,
                ]);
    
                if (!$eligibility) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Failed to insert eligibility data.'], 500);
                }
    
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Form submitted successfully!',
                    'data' => [
                        'student' => $register,
                        'eligibility' => $eligibility
                    ]
                ], 201);
            } catch (ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        }
    }
}
