<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $table        =   'wbscte_other_diploma_results_2024_tbl';
    protected $primaryKey   =   'SL';
    public $timestamps      =   false;

    protected $guarded = [];

    public function institute()
    {
        return $this->hasOne('App\Models\wbscte\Institute', "inst_sl_pk", "INST_ID")->withDefault(function () {
            return new Institute();
        });
    }

    public function course()
    {
        return $this->hasOne('App\Models\wbscte\Course', "course_id_pk", "COURSE_ID")->withDefault(function () {
            return new Course();
        });
    }

    public function student()
    {
        return $this->hasOne('App\Models\wbscte\Student', "student_reg_no", "REG_NO")->where(['student_is_enrolled' => 1, 'student_exam_fees_status' => 1, 'student_session_yr' => '2023-24', 'student_semester' => 'Semester I'])->withDefault(function () {
            return new Student();
        });
    }
}
