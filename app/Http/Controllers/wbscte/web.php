<?php

use App\Models\wbscte\Studentxi;
use Illuminate\Support\Facades\DB;
use App\Models\wbscte\AttendenceXi;
use App\Models\wbscte\MarksEntryXi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\wbscte\AuthController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Artisan;
use Mail;
use App\Mail\TestMail;
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
// this is a comment
Route::post('/authenticate', [AuthController::class, 'authenticate']);

// download pdf
Route::get('/pdf-download', function () {
    $vtc_code = 7;
    $discipline_id = 3;
    $group_id = 204;
    $group_code = 'BCRS';
    $examYear = date('Y');
    $session = sessionYear($examYear);

    $papers = vtcPaperList($vtc_code, $discipline_id, $group_id, $group_code, $examYear);

    $results =  MarksEntryXi::where('vtc_code', $vtc_code)
        ->where('discipline_id', $discipline_id)
        ->where('group_id', $group_id)
        ->where('session_yr', $session)
        ->where('exam_yr', $examYear)
        ->with('studentInfo')
        ->orderBy('sl_no_pk')
        ->get();

    $pdf = Pdf::loadView('exports.resultxi', [
        'papers' => $papers,
        'results' => $results,
    ]);

    return $pdf->setPaper('a4', 'landscape')
        ->setOption(['defaultFont' => 'sans-serif'])
        // ->save('resultpdf.pdf', 'public')
        // ->stream('resultpdf.pdf')
        ->download('resultpdf.pdf');
});

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
    $mobile_no = '9062144661';
    $otp = rand(111111, 999999);
    $sms_message_user = "{$otp} is your One Time Password (OTP). Don't share this with anyone. - WBSCTE&VE&SD";
    $template_id  = 0;
    $response = Http::withQueryParameters([
        'ukey' => 'xa1a8ogxRdKjGM62zMO3yti3P',
        'msisdn' => urlencode($mobile_no),
        'language' => 0,
        'credittype' => 7,
        'senderid' => 'TVESD',
        'templateid' => urlencode($template_id),
        'message' => $sms_message_user,
        'filetype' => 2
    ])->get('http://125.16.147.178/VoicenSMS/webresources/CreateSMSCampaignGet');

    return $response;
});

// generate result UI
Route::get('/result', function () {
    $vtc_code = 7;
    $discipline_id = 3;
    $group_id = 204;
    $group_code = 'BCRS';
    $examYear = date('Y');
    $session = sessionYear($examYear);

    $papers = vtcPaperList($vtc_code, $discipline_id, $group_id, $group_code, $examYear);

    $results =  MarksEntryXi::where('vtc_code', $vtc_code)
        ->where('discipline_id', $discipline_id)
        ->where('group_id', $group_id)
        ->where('session_yr', $session)
        ->where('exam_yr', $examYear)
        ->with('studentInfo')
        ->orderBy('sl_no_pk')
        ->get();

    // return $results;

    return view('exports.resultxi', [
        'papers' => $papers,
        'results' => $results,
    ]);

    // return Excel::download(new ResultXiExport, 'result.xlsx');
});

// create dummy attendence
Route::get('/attendence', function () {
    $vtc_code = 7;
    $discipline_id = 3;
    $group_id = 204;
    $paper = 'BEN1';
    $session = '2023-24';
    $examyear = '2024';

    $papers = ['BEN1', 'ENG1', 'RSE1', 'MDA1', 'ACT1', 'BSM1', 'EDCA'];

    foreach ($papers as $paper) {
        $students = Studentxi::where('student_is_enrolled', 1)
            ->where('student_enrolled_year', $examyear)
            ->where('student_session_yr', $session)
            ->where('student_inst_code', $vtc_code)
            ->where('student_discipline_id_pk', $discipline_id)
            ->where('student_course_id_fk', $group_id)
            ->where('student_class_id', 1)
            ->with('attendences', function ($query) use ($paper) {
                $query->where('exam_xi_att_subj_papr_code', $paper);
            })->get();

        if (sizeof($students) > 0) {
            DB::beginTransaction();

            foreach ($students as $student) {
                AttendenceXi::updateOrCreate(
                    [
                        'exam_xi_att_reg_no' => $student->student_reg_no,
                        'exam_xi_att_subj_papr_code' => $paper
                    ],
                    [
                        'exam_xi_att_session' => $session,
                        'exam_xi_att_exam_year' => $examyear,
                        'exam_xi_att_vtc_code' => $vtc_code,
                        'exam_xi_att_discipline_id' => $discipline_id,
                        'exam_xi_att_group_code' => $group_id,
                        'exam_xi_att_is_present' => true,
                        'exam_xi_att_is_absent' => false,
                        'exam_xi_att_is_ra' => false,
                        'exam_xi_att_is_final_submit' => true,
                        'exam_xi_att_created_on' => now(),
                        'exam_xi_att_created_by' => 1,
                        'exam_xi_att_modified_on' => now(),
                        'exam_xi_att_modified_by' => 1,
                        'exam_xi_att_final_submit_by' => 1
                    ]
                );
            }

            DB::commit();
        }
    }
});

// create dummy marks
Route::get('/marks', function () {
    $vtc = 7;
    $discipline = 3;
    $group = 204;
    $examYear = date('Y');
    $session = sessionYear($examYear);

    $reg_no = AttendenceXi::all()->pluck('exam_xi_att_reg_no');

    $paper1 = 'BEN1';
    $paper2 = 'ENG1';
    $paper3 = 'RSE1';
    $paper4 = 'MDA1';
    $paper5 = 'ACT1';
    $paper6 = 'BSM1';
    $paper7 = 'EDCA';


    foreach ($reg_no as $reg) {
        MarksEntryXi::updateOrCreate([
            'reg_no' => $reg,
        ], [
            'vtc_code' => $vtc,
            'discipline_id' => $discipline,
            'group_id' => $group,
            'session_yr' => $session,
            'exam_yr' => $examYear,


            'p1_code' => $paper1,
            'p1_theory' => rand(30, 60),
            // 'p1_pr_internel' => rand(10, 20),
            // 'p1_pr_externel' => rand(10, 20),
            'p1_project' => rand(10, 20),

            'p2_code' => $paper2,
            'p2_theory' => rand(30, 60),
            'p2_pr_internel' => rand(10, 20),
            'p2_pr_externel' => rand(10, 20),
            // 'p2_project' => rand(10, 20),

            'p3_code' => $paper3,
            'p3_theory' => rand(30, 60),
            'p3_pr_internel' => rand(10, 20),
            'p3_pr_externel' => rand(10, 20),
            // 'p3_project' => rand(10, 20),

            'p4_code' => $paper4,
            'p4_theory' => rand(30, 60),
            'p4_pr_internel' => rand(10, 20),
            'p4_pr_externel' => rand(10, 20),
            // 'p4_project' => rand(10, 20),

            'p5_code' => $paper5,
            'p5_theory' => rand(30, 60),
            // 'p5_pr_internel' => rand(10, 20),
            // 'p5_pr_externel' => rand(10, 20),
            'p5_project' => rand(10, 20),

            'p6_code' => $paper6,
            'p6_theory' => rand(30, 60),
            'p6_pr_internel' => rand(10, 20),
            'p6_pr_externel' => rand(10, 20),
            // 'p6_project' => rand(10, 20),

            'p7_code' => $paper7,
            'p7_theory' => rand(30, 60),
            // 'p7_pr_internel' => rand(10, 20),
            // 'p7_pr_externel' => rand(10, 20),
            'p7_project' => rand(10, 20),

            'is_p1_approved' => true,
            'is_p2_approved' => true,
            'is_p3_approved' => true,
            'is_p4_approved' => true,
            'is_p5_approved' => true,
            'is_p6_approved' => true,
            'is_p7_approved' => true,
        ]);
    }

    // $papers = [
    //     [
    //         'subject_code' => 'BEN1',
    //         'subject_type' => 'paper2'
    //     ],
    //     [
    //         'subject_code' => 'ENG1',
    //         'subject_type' => 'paper2'
    //     ],
    //     [
    //         'subject_code' => 'RSE1',
    //         'subject_type' => 'paper3'
    //     ],
    //     [
    //         'subject_code' => 'MDA1',
    //         'subject_type' => 'paper4'
    //     ],
    //     [
    //         'subject_code' => 'ACT1',
    //         'subject_type' => 'paper5'
    //     ],
    //     [
    //         'subject_code' => 'BSM1',
    //         'subject_type' => 'paper6'
    //     ],
    //     [
    //         'subject_code' => 'EDCA',
    //         'subject_type' => 'paper7'
    //     ]
    // ];


    // foreach ($reg_no as $reg) {
    //     foreach ($papers as $paper) {
    //         MarksEntryXi::create([
    //             'reg_no' => $reg,
    //             'vtc_code' => $vtc,
    //             'discipline_id' => $discipline,
    //             'group_id' => $group,
    //             'session_yr' => $session,
    //             'exam_yr' => $examYear,

    //             'paper_code' => $paper['subject_code'],
    //             'paper_type' => $paper['subject_type'],
    //             'theory' => rand(50, 90),
    //             'prac_internal' => rand(50, 90),
    //             'prac_external' => rand(50, 90),

    //             'is_approved' => true,
    //             'is_final' => true,
    //         ]);
    //     }
    // }



});
// test database connection
Route::post('/test-user', function () {
    $otp = '123456';
    $msg = "This is the otp " . $otp;

    $to = "narayanm@aranaxweb.com";
    $subject = "My subject";
    $txt = "Hello world!";
    $headers = "From: noreply-sctvesd@wbsdc.in" . "\r\n";

    mail($to, $subject, $txt, $headers);

    // $content = [
    //     'subject' => 'This is the mail subject',
    //     'body' => $msg
    // ];
    // echo '<pre>';
    // print_r($content['body']);
    // die();

    //Mail::to('narayanm@aranaxweb.com')->send(new TestMail($content));
    // Mail::send('emails.sample', $content, function ($message) {
    //     $message->subject('Test');   //subject of email
    //     $message->from('support@devstories.in');   // sender email address
    //     $message->to('narayanm@aranaxweb.com');   //receiver email
    // });
    //dd(Mail::failures());
    return "Email has been sent.";
});

Route::post('/test-mail', function () {
});

Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');

    return "Cache cleared successfully";
});
