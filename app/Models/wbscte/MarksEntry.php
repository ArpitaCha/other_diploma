<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarksEntry extends Model
{
    use HasFactory;
    protected $table        =   'wbscte_other_diploma_exam_marks_entry_tbl';
    protected $primaryKey   =   'id';
    public $timestamps      =   false;

    protected $guarded = [];

    public function paperMarks()
    {
        return $this->hasOne('App\Models\wbscte\Paper', "paper_id_pk", "paper_id")->withDefault(function () {
            return new Paper();
        });
    }
    public function student()
    {
        return $this->hasOne('App\Models\wbscte\Student', "student_reg_no", "stud_reg_no")->withDefault(function () {
            return new Student();
        });
    }

    public function institute()
    {
        return $this->hasOne('App\Models\wbscte\Institute', "inst_sl_pk", "inst_id")->withDefault(function () {
            return new Institute();
        });
    }

    public function course()
    {
        return $this->hasOne('App\Models\wbscte\Course', "course_id_pk", "course_id")->withDefault(function () {
            return new Course();
        });
    }
    public function user()
    {
        return $this->hasOne('App\Models\wbscte\User', "u_id","created_by")->withDefault(function () {
            return new User();
        });
    }
}
