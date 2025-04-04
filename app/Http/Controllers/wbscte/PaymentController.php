<?php

namespace App\Http\Controllers\wbscte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\wbscte\RegisterFees;
use App\Models\wbscte\Payment;
use App\Models\wbscte\Student;
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
            $other_data = "{$student_data['student_inst_id']}_{$student_data['student_course_id']}_{$student_data['student_session_yr']}_{$student_data['student_payment_for']}_{$student_data['student_application_form_num']}_{$student_data['student_semester']}_{$total_appl_amount['rf_appl_order_no']}";

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
            $other_data = "{$student_data['student_inst_id']}_{$student_data['student_course_id']}_{$student_data['student_session_yr']}_{$student_data['student_payment_for']}_{$student_data['student_application_form_num']}_{$student_data['student_semester']}_{$total_appl_amount['rf_appl_order_no']}";
        

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
        $other_data = explode('_', $data[6]);

       
        $inst_id = $other_data[0];
        $course_id = $other_data[1];
        $session_year = $other_data[2];
        $paying_for = $other_data[3];
        $form_num = $other_data[4];
        $semester = $other_data[5];
        $order_number = $other_data[6];

        $status = ($paying_for === 'APPLICATION') ? 2 : (($paying_for === 'REGISTRATION') ? 5 : null);

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
   
    

    
}
