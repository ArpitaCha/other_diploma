<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\wbscte\AuthController;
use App\Http\Controllers\wbscte\UsersController;
use App\Http\Controllers\wbscte\CommonController;
use App\Http\Controllers\wbscte\ExaminerController;
use App\Http\Controllers\wbscte\AttendanceController;
use App\Http\Controllers\wbscte\OtherController;
use App\Http\Controllers\wbscte\MarksEntryController;
use App\Http\Controllers\wbscte\ResultProcessController;
use App\Http\Controllers\wbscte\DashboardNotificationController;
use App\Http\Controllers\wbscte\DashboardController;
use App\Http\Controllers\wbscte\EnrollmentController;
use App\Http\Controllers\wbscte\AdmissionController;
use App\Http\Controllers\wbscte\PaymentController;
use App\Http\Controllers\wbscte\VenueAllocationController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/authenticate', [AuthController::class, 'authenticate']);
Route::post('/validate-security-code', [AuthController::class, 'validateSecurityCode']);
Route::get('/logout/{user_id}', [AuthController::class, 'logout']);
Route::prefix('master')->group(function () {
    Route::get('/user-list', [UsersController::class, 'allUsers']);
    // Route::get('/state-list/{user_type?}', [CommonController::class, 'allStates']);
    Route::get('/district-list/{state_code?}/{user_type?}', [CommonController::class, 'allDistricts']);
    Route::get('/state-list/{user_type?}', [CommonController::class, 'allStates']);
    Route::get('/subdivision-list/{dist_id?}/{user_type?}', [CommonController::class, 'allSubdivisions']);
    Route::get('/get-all-institute-list/{inst_id?}/{user_type?}', [CommonController::class, 'allInstList']);
    Route::get('/all-course/{inst_id?}/{user_type?}', [CommonController::class, 'allCourseList']);
    Route::post('/course-list/{user_type?}', [CommonController::class, 'instwiseCourse']);
    Route::get('/all-theory-paper', [CommonController::class, 'allTheoryPaperList']);
    Route::get('/active-session/{type}', [CommonController::class, 'activeSession']);
    Route::post('/institute-list', [CommonController::class, 'userWiseInstitute']);
    Route::post('/update-institute', [CommonController::class, 'updateInstitute']);
    Route::get('/edit-course/{id}', [CommonController::class, 'editCourse']);
    Route::post('/update-course', [CommonController::class, 'updateCourse']);
    Route::post('/paper-list', [CommonController::class, 'instCourseSessionSemPaperTypewisePaperlist']);
    Route::post('/examiner-list', [CommonController::class, 'examinerList']);
    Route::post('/check-paper', [CommonController::class, 'checkPaper']);
    Route::post('/semester-list', [CommonController::class, 'semesterList']);
    Route::post('/paper-type-list', [CommonController::class, 'paperTypeList']);
    Route::post('/entry-type-list', [CommonController::class, 'entryTypeList']);
    Route::post('/institute-wise-examiner', [CommonController::class, 'instituteWiseExaminer']);
    Route::post('/create-cnfg-marks', [CommonController::class, 'createConfigMarks']);
    Route::post('/update-cnfg-marks', [CommonController::class, 'updateConfigMarks']);
    Route::delete('/delete-cnfg-marks/{id}', [CommonController::class, 'deleteConfigMarks']);
    Route::post('/get-cnfg-marks', [CommonController::class, 'getConfigMarks']);
    Route::post('/check-cnfg-schedule', [CommonController::class, 'checkScheduleConfig']);
    Route::post('/examiner-notexist-institute',[CommonController::class, 'examinerNotExistInstitute']);
    Route::post('/create-user',[CommonController::class, 'createUser']);
    Route::post('/create-paper',[CommonController::class,'createPaper']);
    Route::post('/create-course',[CommonController::class,'createCourse']);
    Route::post('/create-institute',[CommonController::class,'createInstitute']);
    Route::post('/update-paper', [CommonController::class, 'updatePaper']);
    Route::get('/edit-paper/{id}', [CommonController::class, 'editPaper']);
    Route::post('/count-dashboard', [CommonController::class, 'countDashboard']);
    Route::post('/eligibility-list/{user_type?}',[CommonController::class, 'eligibilityList']);
    Route::post('/eligibility-match',[CommonController::class, 'eligibilityMatch']);

});
Route::prefix('examiner')->group(function () {
    Route::post('/examiner-entry', [ExaminerController::class, 'examinerEntry']);
    Route::post('/internal-examiner-tag', [ExaminerController::class, 'internelExaminerTag']);
    // Route::post('/internal-examiner-tag-list', [ExaminerController::class, 'internalExaminerTagList']);
    Route::get('/internal-examiner-tag-list/{session_yr?}/{semester?}', [ExaminerController::class, 'internalExaminerTagList']);
    Route::get('/internal-examiner-tag-show/{id?}', [ExaminerController::class, 'internalExaminerTagShow']);
    Route::post('/internal-examiner-update', [ExaminerController::class, 'updateInternalExaminer']);
    Route::post('/internal-examiner-info-update', [ExaminerController::class, 'updateInternalExaminerEntry']);
    // Route::post('/internal-examiner-assign-list', [ExaminerController::class, 'InternalExaminerAssignList']);
    Route::get('/internal-examiner-assign-list/{session_yr?}/{semester?}', [ExaminerController::class, 'InternalExaminerAssignList']);

    Route::post('/internal-examiner-list', [ExaminerController::class, 'internalExamierList']);
    Route::post('/taggable-institution-list', [ExaminerController::class, 'taggableInstitutionList']);
    Route::post('/external-examiner-tag', [ExaminerController::class, 'externelExaminerTag']);
    Route::get('/external-examiner-list/{session_yr?}/{semester?}', [ExaminerController::class, 'externelExaminerList']);
    Route::post('/internal-examinerwise-institute-list', [ExaminerController::class, 'internalExamierWiseInstituteList']);
    Route::post('/special-examiner-entry', [ExaminerController::class, 'specialExaminerEntry']);
    Route::post('/special-examiner-tag', [ExaminerController::class, 'specialExaminerTag']);
    Route::get('/internal-examiner-details/{examiner_id?}', [ExaminerController::class, 'internalExaminerDetails']);
    Route::get('/special-examiner-list/{session_yr?}/{semester?}', [ExaminerController::class, 'specialExaminerList']);
});


Route::prefix('dashboard')->group(function () {
    Route::get('/student-detail/{student_id}', [DashboardController::class, 'studentDetail']);//dashboard/student-detail
});
Route::prefix('semester-i')->group(function () {
    Route::prefix('enrollment')->group(function () {
        Route::post('/list', [EnrollmentController::class, 'list']);
        Route::post('/submit', [EnrollmentController::class, 'submit']);
        Route::post('/unlock', [EnrollmentController::class, 'update']);
        Route::post('/enroll-pdf', [EnrollmentController::class, 'downloadPdf']);
    });
    Route::prefix('attendance')->group(function () {
        Route::post('/list', [AttendanceController::class, 'list']);
        Route::post('/individual-attendance', [AttendanceController::class, 'individualAttendance']);
        Route::post('/final-submit', [AttendanceController::class, 'finalAttendanceSubmit']);
        Route::post('/lock', [AttendanceController::class, 'attendanceLock']);
    });
    Route::prefix('marks-entry')->group(function () {
        Route::post('/list', [MarksEntryController::class, 'marksentrylist']);
        Route::post('/update', [MarksEntryController::class, 'marksUpdate']);
        Route::post('/final-submit', [MarksEntryController::class, 'marksFinalSubmit']);
        Route::get('/pdf', [MarksEntryController::class, 'marksPdf']);
        Route::post('/lock',[MarksEntryController::class, 'marksLock']);
    });
});
Route::prefix('admission')->group(function () {
    Route::post('/semone-admission-form', [AdmissionController::class, 'semOneadmissionFormSubmit']);
    Route::get('/view-student/{form_num}/{inst_id?}', [AdmissionController::class, 'viewStudentData']);
    Route::get('/admission-list/{inst_id?}', [AdmissionController::class, 'admissionListByInstitute']);
    Route::post('/appl-verify-status', [AdmissionController::class, 'applicationVerificationInstitute']);
    Route::post('/upload-image-sign', [AdmissionController::class, 'uploadImageSign']);
    Route::post('/approve-council', [AdmissionController::class, 'approvedCouncil']);
    Route::post('/approve-all', [AdmissionController::class, 'approvedAll']);
    Route::post('/update-student', [AdmissionController::class, 'updateStudent']);
    // Route::post('send-mail', [AdmissionController::class, 'sendMail']);


});
Route::prefix('payment')->group(function () {
    Route::post('/pay-application-fees', [PaymentController::class, 'payApplicationFees']);
    Route::post('/pay-registration-fees', [PaymentController::class, 'payRegistrationFees']);
    Route::post('/pay-enrollment-fees', [PaymentController::class, 'payEnrollmentFees']);
});
Route::prefix('venue-allocation')->group(function () {
    Route::post('/venue-list', [VenueAllocationController::class, 'list']);
});


Route::get('/student-attendance-script', [OtherController::class, 'defaultStudentAttendance']);
//Result process script
Route::get('/student-result-process-step-1', [ResultProcessController::class, 'resultProcessScriptStep1']);
Route::get('/student-result-process-step-2', [ResultProcessController::class, 'resultProcessScriptStep2']);
Route::get('/student-result-process-step-3', [ResultProcessController::class, 'resultProcessScriptStep3']);
Route::get('/student-result-process-step-4', [ResultProcessController::class, 'resultProcessScriptStep4']);
Route::get('/student-result-process-step-5', [ResultProcessController::class, 'resultProcessScriptStep5']);
Route::get('/student-result-process-step-6', [ResultProcessController::class, 'resultProcessScriptStep6']);
Route::get('/student-result-process-step-7', [ResultProcessController::class, 'resultProcessScriptStep7']);

Route::get('/result-pdf', [OtherController::class, 'resultPdf']);
Route::get('/application-fees-pdf/{form_num}', [OtherController::class, 'applicationFeesPdf']);
Route::get('/registration-fees-pdf/{form_num}', [OtherController::class, 'registrationFeesPdf']);
Route::get('/registration-pdf', [OtherController::class, 'registrationPdf']);
Route::get('/student-details-pdf/{form_num}', [OtherController::class, 'studentDetailsPdf']);


//Dashboard Notification
Route::post('/create-notification', [DashboardNotificationController::class, 'createNotification']);
Route::get('/notification-list', [DashboardNotificationController::class, 'notificationList']);


Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');

    return "Cache cleared successfully";
});
