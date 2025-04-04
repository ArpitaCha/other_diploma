<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\StudentMail;
use App\Mail\StudentStatusMail;

class SendStudentEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
   
     */
    protected $signature = 'app:send-student-emails';
    protected $description = 'Send emails to students based on their status';
    public function __construct()
    {
        parent::__construct();
    }



    /**
     * Execute the console command.
     */
    public function handle()
    {
        {
            $students = Student::whereIn('student_status_s1', [1, 3, 6, 9])->get();
    
            foreach ($students as $student) {
                try {
                    switch ($student->student_status_s1) {
                        case 1:
                            Mail::to($student->student_email)->send(new StudentMail($student->name, $student->form_num, $student->student_email));
                            auditTrail($student->form_num, $student->form_num . ' - Application approved and confirmation email sent');
                            break;
    
                        case 3:
                        case 9:
                            Mail::to($student->student_email)->send(new StudentStatusMail($student->form_num, $student->student_email, $student->institute->institute_name));
                            auditTrail($student->form_num, $student->form_num . ' - Institute approved and confirmation email sent');
                            break;
    
                        case 6:
                            Mail::to($student->student_email)->send(new StudentStatusMail($student->form_num, $student->student_email, $student->institute->institute_name, $student->reg_no));
                            auditTrail($student->form_num, $student->reg_no . ' - Council admin approved and confirmation email sent');
                            break;
                    }
                } catch (\Exception $e) {
                    \Log::error('Email sending failed for student: ' . $student->id . ' Error: ' . $e->getMessage());
                }
            }
    
            $this->info('Student emails sent successfully.');
        }
    }
}
