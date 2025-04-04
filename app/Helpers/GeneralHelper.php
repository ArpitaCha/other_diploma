<?php

use Illuminate\Support\Str;
use App\Models\wbscte\AuditTrail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\wbscte\Student;
use App\Models\wbscte\Course;

if (!function_exists('generateLaravelLog')) {

    function generateLaravelLog($e)
    {
        $routeArray = app('request')->route()->getAction();
        $controllerAction = class_basename($routeArray['controller']);
        list($controller, $action) = explode('@', $controllerAction);

        Log::info($controller . '||' . $action . ' ERROR-' . $e->getMessage()

            . "\nFile path :" . $e->getFile()
            . "\nline no :" . $e->getLine());
        // dd($controller, $action);
    }
}

if (!function_exists("auditTrail")) {
    function auditTrail($user_id, $task)
    {
        AuditTrail::create([
            'audittrail_user_id' => $user_id,
            'audittrail_ip' => request()->ip(),
            'audittrail_task' => $task,
            'audittrail_date' => now()
        ]);
    }
}

if (!function_exists("searchAssociative")) {
    function searchAssociative($arr, $key, $value)
    {
        foreach ($arr as $data) {
            if ($data[$key] == $value) {
                return true;
            } else {
                return false;
            }
        }
    }
}

if (!function_exists('sessionYear')) {
    function sessionYear($year)
    {
        $a = $year - 1;
        $b = Str::charAt($year, 2) . Str::charAt($year, 3);

        return "{$a}-{$b}";
    }
}

//CHANGE DATE FORMATE OF A DATE
if (!function_exists('formatDate')) {
    function formatDate($date, $fromFormat = 'Y-m-d', $toFormat = 'd-M-Y')
    {
        $dt = new DateTime();
        if ($date != null) {
            $datetime = $dt->createFromFormat($fromFormat, $date)->format($toFormat);
            return $datetime;
        } else {
            return '---';
        }
    }
}

//generate otp
if (!function_exists('generateOTP')) {
    function generateOTP()
    {
        $possible_letters = '1234567890';
        $code = '';
        for ($x = 0; $x < 6; $x++) {
            $code .= ($num = substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1));
        }
        return $code;
    }
}

//get time difference in minute
if (!function_exists('getTimeDiffInMinute')) {
    function getTimeDiffInMinute($time1, $time2)
    {
        $minutes = (strtotime($time1) - strtotime($time2)) / 60;

        return $minutes;
    }
}

//send Sms
if (!function_exists('send_sms')) {
    function send_sms($phone_to, $sms_message)
    {
        $template_id  = 0;
        $response = Http::withQueryParameters([
            'ukey' => 'xa1a8ogxRdKjGM62zMO3yti3P',
            'msisdn' => urlencode($phone_to),
            'language' => 0,
            'credittype' => 7,
            'senderid' => 'TVESD',
            'templateid' => urlencode($template_id),
            'message' => $sms_message,
            'filetype' => 2
        ])->get('http://125.16.147.178/VoicenSMS/webresources/CreateSMSCampaignGet');

        return $response;
    }
}

if (!function_exists('sessionYear')) {
    function sessionYear($year)
    {
        $a = (int)$year - 1;
        $b = Str::charAt($year, 2) . Str::charAt($year, 3);

        return "{$a}-{$b}";
    }
}

if (!function_exists('getFinancialYear')) {
    function getFinancialYear($currentSession, $type = "")
    {
        $current = explode("-", $currentSession);

        $y = $current[0];
        $yy = $current[1];
        $m = date('m');

        $financial_year = array();
        if ($type == 'regular') {
            $year = $y . '-' . ($yy);
            array_push($financial_year, $year);
        } elseif ($type == 'continuing') {
            for ($i = 0; $i <= 2; $i++) {
                if ($i == 0) {
                    $year = $y - 1 . '-' . ($yy - 1);
                    array_push($financial_year, $year);
                } else if ($i == 1) {
                    $year = ($y - ($i + 1)) . '-' . ($yy - 2);
                    array_push($financial_year, $year);
                }
            }
        } else {
            for ($i = 0; $i <= 2; $i++) {
                if ($i == 0) {
                    $year = $y . '-' . ($yy);
                    array_push($financial_year, $year);
                } else if ($i == 1) {
                    $year = ($y - $i) . '-' . ($yy - 1);
                    array_push($financial_year, $year);
                } else {
                    $year = ($y - $i) . '-' . ($yy - 2);
                    array_push($financial_year, $year);
                }
            }
        }

        return $financial_year;
    }
}

if (!function_exists('generateRandomCode')) {
    function generateRandomCode()
    {
        $now = date('Y-m-d H:i:s');
        $code = md5($now . rand(6, 9));
        return $code;
    }
}




if (!function_exists('generateApplicationNumber')) {
    function generateApplicationNumber()
    {
        $yearLastTwoDigits = date('y'); 
        $latestApplication = DB::table('wbscte_other_diploma_student_master_tbl')
            ->where('student_form_num', 'LIKE', "APP{$yearLastTwoDigits}%")
            ->orderByDesc('student_form_num') 
            ->first();
        $lastNumber = $latestApplication ? intval(substr($latestApplication->student_form_num, 5)) : 0;
        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        return "APP{$yearLastTwoDigits}{$newNumber}";
    }
}
if (!function_exists('generateRegistrationNo')) {
    function generateRegistrationNo($form_num)
    {
        // Fetch student record
        $student_data = Student::where('student_form_num', $form_num)->first();
        if (!$student_data) {
            return null; // Handle case when student not found
        }
        
        $course = Course::where('course_id_pk', $student_data->student_course_id)
            ->value('course_code');
        $session_year = $student_data->student_session_yr;
        $output = str_replace("-", "", substr($session_year, 2)); 
        $lastStudent = Student::where('student_reg_no', 'LIKE', "{$course}{$output}%")
            ->orderBy('student_reg_no', 'desc')
            ->first();
        $lastNumber = $lastStudent ? intval(substr($lastStudent->student_reg_no, -5)) : 0;
        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        return "{$course}{$output}{$newNumber}";
    }
}

// SBI EPAY
function sbiEncrypt($data)
{
    $key = env('SBI_PAYMENT_KEY');

    $cipherText = openssl_encrypt(
        $data,
        'aes-128-cbc',
        $key,
        OPENSSL_RAW_DATA,
        substr($key, 0, 16)
    );

    return base64_encode($cipherText);
}

function sbiDecrypt($data)
{
    $key = env('SBI_PAYMENT_KEY');

    $plaintext = openssl_decrypt(
        base64_decode($data),
        'aes-128-cbc',
        $key,
        OPENSSL_RAW_DATA,
        substr($key, 0, 16)
    );

    return $plaintext;
}
function getPaymentData($orderid, $pay_amount, $other_data)
{
    $marid = '5';
    $marchent_id = env('SBI_PAYMENT_MERCHANT_ID');
    $api_key = env('SBI_PAYMENT_API');

    $base_url = env('APP_URL') . "/payment/";
    $success_url = "{$base_url}success";
    $fail_url =  "{$base_url}fail";

    $paymentData = "{$marchent_id}|DOM|IN|INR|{$pay_amount}|{$other_data}|{$success_url}|{$fail_url}|SBIEPAY|{$orderid}|{$marid}|NB|ONLINE|ONLINE,pWhMnIEMc4q6hKdi2Fx50Ii8CKAoSIqv9ScSpwuMHM4=";

    return [
        'transaction_id' => sbiEncrypt($paymentData),
        'marchant_id' => $marchent_id,
        'payment_api' => $api_key
    ];
}
