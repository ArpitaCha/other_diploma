<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\wbscte\RegisterFees;
use App\Models\wbscte\Payment;
use App\Models\wbscte\Student;
use App\Models\wbscte\Enrollment;
use App\Models\wbscte\ExamRoll;
use App\Models\wbscte\Fees;
use App\Models\wbscte\PaymentTransaction;
class PaymentController extends Controller
{
    public function payApplicationFees(Request $request){
        $student_data = $request->student_info;

        $total_appl_amount = RegisterFees::select('rf_fees_amount','rf_appl_order_no')
                            ->where('rf_appl_form_num', $student_data['student_application_form_num'])
                            ->where('rf_semester', $student_data['student_semester'])
                            ->where('rf_fees_type', $student_data['student_payment_for'])->first();
                            

        //dd($total_appl_amount->rf_fees_amount);
        $amount = $total_appl_amount->rf_fees_amount;
        if ($amount > '0') {
            $other_data = "{$student_data['student_inst_id']}_{$student_data['student_course_id']}_{$student_data['student_session_yr']}_{$student_data['student_payment_for']}_{$student_data['student_application_form_num']}_{$student_data['student_semester']}_{$total_appl_amount['rf_appl_order_no']}_{$student_data['student_reg_no']}";

            $orderno = $total_appl_amount->rf_appl_order_no;
            $orderid = '';
            for ($i = 0; $i < 10; $i++) {
                $d = rand(1, 30) % 2;
                $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
                $orderid .= $d;
            }
            PaymentTransaction::create([
                'order_id' => $orderid,
                'order_number' => $orderno,
                'initiated_by' => $student_data['student_application_form_num'],
                'initiated_at' => now(),
                'paying_for' => $student_data['student_payment_for'],
                'semester' => $student_data['student_semester'],
                'course_id' => $student_data['student_course_id'],
                'trans_amount' => $amount
            ]);
            auditTrail($student_data['student_application_form_num'], "Payment initiated by students: {$student_data['student_application_form_num']} with order id : {$orderid} for {$student_data['student_payment_for']}");

            return response()->json([
                'error' => false,
                'message' => 'Payment Data Found',
                'payment_data' => getPaymentData($orderid, $amount, $other_data)
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Something went wrong, Try Again Later'
            ]);
        }

    }
    public function payRegistrationFees(Request $request)
    {
        $student_data = $request->student_info;
       

        $total_appl_amount = RegisterFees::select('rf_fees_amount','rf_appl_order_no')
                            ->where('rf_appl_form_num', $student_data['student_application_form_num'])
                            ->where('rf_semester', $student_data['student_semester'])
                            ->where('rf_fees_type', $student_data['student_payment_for'])->first();
                            // dd($total_appl_amount);

        //dd($total_appl_amount->rf_fees_amount);
        $amount = $total_appl_amount->rf_fees_amount;

        if ($amount > '0') {
            $other_data = "{$student_data['student_inst_id']}_{$student_data['student_course_id']}_{$student_data['student_session_yr']}_{$student_data['student_payment_for']}_{$student_data['student_application_form_num']}_{$student_data['student_semester']}_{$total_appl_amount['rf_appl_order_no']}_{$student_data['student_reg_no']}";
        

            $orderno = $total_appl_amount->rf_appl_order_no;
            $orderid = '';
            for ($i = 0; $i < 10; $i++) {
                $d = rand(1, 30) % 2;
                $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
                $orderid .= $d;
            }
            PaymentTransaction::create([
                'order_id' => $orderid,
                'order_number' => $orderno,
                'initiated_by' => $student_data['student_application_form_num'],
                'initiated_at' => now(),
                'paying_for' => $student_data['student_payment_for'],
                'semester' => $student_data['student_semester'],
                'course_id' => $student_data['student_course_id'],
                'trans_amount' => $amount
            ]);
            auditTrail($student_data['student_application_form_num'], "Payment registration by students: {$student_data['student_application_form_num']} with order id : {$orderid} for {$student_data['student_payment_for']}");

            return response()->json([
                'error' => false,
                'message' => 'Payment Data Found',
                'payment_data' => getPaymentData($orderid, $amount, $other_data)
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Something went wrong, Try Again Later'
            ]);
        }

    }
    public function payEnrollmentFees(Request $request)
    {
            $student_data = $request->student_info;
           
            $exam_year = $request->exam_year;
            $user_id = $request->u_id;
            $inst_id=$request->inst_id;
            $course_id=$request->course;
            $paying_for=$request->paying_for;
           
            $semester='SEMESTER_I';
    
            $reg_array = collect($request->student_info)->pluck('reg_no')->toArray();
            // dd($reg_array);
            
    
             $fees_data = Fees::where([
            'inst_id' => $inst_id,
            'course_id' => $course_id,
            'type' => $paying_for,
            'semester' => $semester,
        ])->whereIn('reg_no', $reg_array);
         
            $total_amount = $fees_data->sum('amount');
            //  dd($total_amount);
            $reg_list = implode(',', $reg_array);
            $other_data = [];

    
            if ($total_amount) {
                 foreach ($student_data as $student) {
                    $form_num = $student['student_form_num'] ?? 'NA';
                    $reg_no = $student['student_reg_no'] ?? 'NA';

                    $other_data[] = "{$inst_id}_{$course_id}_{$exam_year}_{$paying_for}_{$form_num}_{$semester}_{$total_amount}_{$reg_no}";
                 }
    
                $orderid = '';
                for ($i = 0; $i < 10; $i++) {
                    $d = rand(1, 30) % 2;
                    $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
                    $orderid .= $d;
                }
    
                PaymentTransaction::create([
                    'order_id' => $orderid,
                    'initiated_by' => $user_id,
                    'initiated_at' => now(),
                    'paying_for' => $paying_for,
                    'course_id' => $course_id,
                    'trans_amount' => $total_amount,
                    // 'inst_id' => $inst_id,
                ]);
  
                $fees_data->update([
                    'order_id' => $orderid,
                ]);
    
                auditTrail($user_id, "Payment initiated for students: {$reg_list} with order id : {$orderid} for {$paying_for}");
    
                return response()->json([
                    'error' => false,
                    'message' => 'Payment Data Found',
                    'payment_data' => getPaymentData($orderid, $total_amount, $other_data)
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Something went wrong, Try Again Later'
                ]);
            }
        
    
    }
    public function paymentSuccess(Request $request)
    {
     
        // Merchant Order Number|SBIePayRefID/ATRN|Transaction Status|Amount|Currency|Pay Mode|Other Details|Reason/Message|Bank Code|Bank Reference Number|Transaction Date|Country|CIN|Merchant ID|Total Fee GST |Ref1|Ref2|Ref3|Ref4|Ref5|Ref6|Ref7|Ref8|Ref9
        try {
        $trans_details = sbiDecrypt($request->encData);
        $data = explode('|', $trans_details);
        $order_id = $data[0];
        $trans_id = $data[1];
        $trans_status = $data[2];
        $trans_amount = $data[3];
        $currency = $data[4];
        $trans_mode = $data[5];
        $message = $data[7];
        $trans_time = $data[10];
        $marchnt_id = $data[13];
        $other_data = explode('_', $data[7]);

       
        $inst_id = $other_data[0];
        $course_id = $other_data[1];
        $session_year = $other_data[2];
        $paying_for = $other_data[3];
        $form_num = $other_data[4];
        $semester = $other_data[5];
        $order_number = $other_data[6];
        if (in_array($paying_for, ['ENROLLMENT'])) {
            $reg_no_arr = explode(',', $other_data[7]);
        } else {
            $reg_no_arr = [$other_data[7]];
        }
        $map = [
            'APPLICATION' => 2,
            'REGISTRATION' => 5,
            'ENROLLMENT' => 7,
        ];
        
        $status = $map[$paying_for] ?? null;

        if ($status !== null) {
            Student::where([
                'student_form_num'=> $form_num,
                'student_semester'=> $semester,
            ])->update([
                'student_status_s1' => $status
            ]);
        }
            $tranction = PaymentTransaction::where('order_id', $order_id)->first();

            if ($tranction) {
                $tranction->update([
                    'trans_id' => $trans_id,
                    'trans_status' => $trans_status,
                    'trans_amount' => $trans_amount,
                    'trans_mode' => $trans_mode,
                    'trans_time' => $trans_time,
                    'marchnt_id' => $marchnt_id,
                    // 'trans_details' => $trans_details,
                    'is_verified' => 1,
                    'order_number'=>$order_number
                ]);

                Payment::create([
                    'order_id' => $order_id,
                    'trans_id' => $trans_id,
                    'paid_type' => $paying_for,
                    'paid_amount' => $trans_amount,
                    'paid_at' => $trans_time,
                    'payment_mode' => $trans_mode,
                    'detail' => $trans_details,
                    'form_no' =>  $form_num,
                    'semester' => $semester
                ]);
                EnrollmentDetail::where([
                    'inst_id' => $inst_id,
                    'academic_year' => $academic_year,
                    'semester' => $semester,
                    'course_id' => $course,
                    'session_year' => $academic_year,
                ])->whereIn('reg_no', $reg_no_arr)
                    ->update([
                        'is_paid' => 1,
                        'is_eligible_for_exam' => 1
                    ]);
                    Student::where([
                        'student_inst_id' => $inst_id,
                        'student_session_yr' => $academic_year,
                        'student_semester' => $semester,
                        'student_course_id' => $course,
                       
                    ])->whereIn('reg_no', $reg_no_arr)
                        ->update([
                            'student_exam_fees_status' => 1,
                            'student_eligible_for_exam' => 1
                        ]);
                    
                    $this->rollnoGenerate($user_id, $inst_id, $course_id,$academic_year, $semester, $reg_no_arr, $reg_year,$exam_year);
                    if ($paying_for === 'EXAMINATION') {
                        auditTrail($reg_no_arr, "Exam fee payment {$trans_status} for reg No are: {$reg_no_arr}, ORDER ID: {$order_id}, TRANSACTION ID: {$trans_id}");
                    } else {
                        auditTrail($form_num, "Payment {$trans_status} for Application No: {$form_num}, ORDER ID: {$order_id}, TRANSACTION ID: {$trans_id}");
                    }
                // auditTrail($form_num, "Payment {$trans_status} for Application No: {$form_num}, ORDER ID: {$order_id}, TRANSACTION ID: {$trans_id}");

                return redirect()->route('payment.redirect', [
                    'trans_id' => $trans_id,
                    'order_id' => $order_id,
                    'paying_for' => $paying_for,
                    'message' => $message,
                    'currency' => $currency,
                    'trans_amount' => $trans_amount,
                    'trans_time' => date('d-m-Y h:i a', strtotime($trans_time)),
                    'trans_status' => $trans_status,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
        
    }
    public function paymentFail(Request $request)
    {
        // Merchant Order Number|SBIePayRefID/ATRN|Transaction Status|Amount|Currency|Pay Mode|Other Details|Reason/Message|Bank Code|Bank Reference Number|Transaction Date|Country|CIN|Merchant ID|Total Fee GST |Ref1|Ref2|Ref3|Ref4|Ref5|Ref6|Ref7|Ref8|Ref9
        try{
            $trans_details = sbiDecrypt($request->encData);
            $data = explode('|', $trans_details);
            $order_id = $data[0];
            $trans_id = $data[1];
            $trans_status = $data[2];
            $trans_amount = $data[3];
            $currency = $data[4];
            $trans_mode = $data[5];
            $message = $data[7];
            $trans_time = $data[10];
            $marchnt_id = $data[13];
            $other_data = explode('_', $data[6]);
            $inst_id = $other_data[0];
            $course_id = $other_data[1];
            $session_year = $other_data[2];
            $paying_for = $other_data[3];
            $form_num = $other_data[4];
            $semester = $other_data[5];
            $order_number = $other_data[6];
            $tranction = PaymentTransaction::where('order_id', $order_id)->first();

            if ($tranction) {
                $tranction->update([
                    'trans_id' => $trans_id,
                    'trans_status' => $trans_status,
                    'trans_amount' => $trans_amount,
                    'trans_mode' => $trans_mode,
                    'trans_time' => $trans_time,
                    'marchnt_id' => $marchnt_id,
                    // 'trans_details' => $trans_details,
                    'is_verified' => 1,
                ]);

                auditTrail($form_num, "Payment {$trans_status} for Application No: {$form_num}, ORDER ID: {$order_id}, TRANSACTION ID: {$trans_id}");
                return redirect()->route('payment.redirect', [
                    'trans_id' => $trans_id,
                    'order_id' => $order_id,
                    'paying_for' => $paying_for,
                    'message' => $message,
                    'currency' => $currency,
                    'trans_amount' => $trans_amount,
                    'trans_time' => date('d-m-Y h:i a', strtotime($trans_time)),
                    'trans_status' => $trans_status,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }
    private function attendanceCreate($user_id, $inst_id, $course_id,$academic_year, $semester, $reg_no_arr, $reg_year,$exam_year)
    {
        $students = Enrollment::where([
            'semester' => $semester,
            'academic_session' => $academic_year,
            'inst_id' => $inst_id,
            'course_id' => $course_id,
            'exam_year' => $exam_year
        ])->whereIn('reg_no', $reg_no_arr)
            ->withCount([
                'attendences' => function (Builder $query) use ($academic_year, $semester) {
                    $query->where([
                        'attr_sessional_yr' => $academic_year,
                        'semester' => $semester,
                    ]);
                }
            ])
            ->get();

        foreach ($students as $student) {
            if ($student->attendences_count == 0) {
                $papers = Paper::select('inst_id', 'course_id','paper_semester')
                ->where([
                    'inst_id' => $inst_id,
                    'course_id' => $course_id,
                    'paper_semester'=> $semester,
                    'is_active' => 1
                ])->get();
                
                    foreach ($papers as $paper) {
                        
                        foreach ([1, 2] as $entryType) {
                            Attendance::updateOrCreate([
                                'attr_sessional_yr' => $academic_year,
                                'att_inst_id' => $inst_id,
                                'att_course_id' => $course_id,
                                'att_reg_no' => $student->reg_no,
                                'att_sem' => $semester,
                                'att_paper_id' => $paper->paper_id_pk,
                                'att_paper_type' => $paper->paper_category,
                                'att_paper_entry_type' => $entryType
                            ], [
                                'att_is_present' => true,
                                'att_is_absent' => false,
                                'att_is_ra' => false,
                                'att_created_on' => now(),
                                'att_modified_by' => $user_id,
                            ]);
                        }
                    }

                // }
                
            }
        }
    }
    private function rollnoGenerate($user_id, $inst_id, $course_id,$academic_year, $semester, $reg_no_arr, $reg_year,$exam_year)
    {
        $students = Enrollment::where([
            'semester' => $semester,
            'academic_session' => $academic_year,
            'inst_id' => $inst_id,
            'course_id' => $course_id,
            'exam_year' => $exam_year
        ])->whereIn('reg_no', $reg_no_arr)->get();
        foreach($students as $value)
        {
            $rollNo = generateRollNo($exam_year, $inst_id);
            ExamRoll::create([
                'reg_no' => $value->reg_no,
                'roll_no' => $rollNo,
                'semester' => $value->semester,
                'session_yr' => $value->academic_session,
                'inst_id' => $value->inst_id,
                'course_id' => $value->course_id,
                'exam_year' => $value->exam_year,
                'created_at'=> now(),
                'created_by'=>$user_id
            ]);
            

        }

    }
   
    

    
}
