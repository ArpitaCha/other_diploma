<?php

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\wbscte\Studentxi;
use Illuminate\Support\Facades\DB;
use App\Models\wbscte\AttendenceXi;
use App\Models\wbscte\MarksEntryXi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\wbscte\AuthController;
use App\Http\Controllers\wbscte\OtherController;
use App\Http\Controllers\wbscte\PaymentController;
use App\Http\Controllers\wbscte\AdmissionController;
use App\Mail\TestMail;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test', function () {
    $uuid = Str::uuid()->toString();
    echo $uuid;
});

Route::post('/authenticate', [AuthController::class, 'authenticate']);

// test database connection
Route::get('/test-database', function () {
    try {
        DB::connection()->getPdo();
        echo "Connected successfully to the database!";
    } catch (\Exception $e) {
        die("Could not connect to the database. Error: " . $e->getMessage());
    }
});

// send OTP to phone
Route::get('smsotp', function () {
    $otp = rand(100000, 999999);
    $phone_to = 7872289842;
    $template_id  = 0;
    $sms_message = "{$otp} is your One Time Password (OTP). Don't share this with anyone. - WBSCTE&VE&SD";

    $template_id  = 0;
    $response = Http::withoutVerifying()
        ->withQueryParameters([
            'ukey' => 'xa1a8ogxRdKjGM62zMO3yti3P',
            'msisdn' => urlencode($phone_to),
            'language' => 0,
            'credittype' => 7,
            'senderid' => 'TVESD',
            'templateid' => urlencode($template_id),
            'message' => $sms_message,
            'filetype' => 2
        ])->get('http://125.16.147.178/VoicenSMS/webresources/CreateSMSCampaignGet');
    $res =  $response->getStatusCode();
    $res1 =  $response->getReasonPhrase();
    $data = ["status" => $res, "message" => $res1];
    return $data;
});
//Payment test
Route::prefix('payment')->group(function () {
    Route::post('/success', [PaymentController::class, 'paymentSuccess']);
    Route::post('/fail', [PaymentController::class, 'paymentFail']);
    Route::post('/push', [PaymentController::class, 'paymentPush']);
});


// payment Redirect page
Route::get('payment-redirect/{trans_id}/{order_id}/{paying_for}/{message}/{currency}/{trans_amount}/{trans_time}/{trans_status}', function ($trans_id, $order_id, $paying_for, $message, $currency, $trans_amount, $trans_time,$trans_status) {
    return view('redirect.Payment', [
        'trans_id' => $trans_id,
        'order_id' => $order_id,
        'paying_for' => $paying_for,
        'message' => $message,
        'currency' => $currency,
        'trans_amount' => $trans_amount,
        'trans_time' => $trans_time,
        // 'appl_num' => $appl_num,
        'trans_status' => $trans_status,
    ]);
})->name('payment.redirect');

Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');

    return "Cache cleared successfully";
});

// Route::get('/sendmail', function () {
//     Mail::to('abcd@gmail.com')->send(new TestMail());
// });
Route::post('send-mail', [AdmissionController::class, 'sendMail']);
