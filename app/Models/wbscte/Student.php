<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table        =   'wbscte_other_diploma_student_master_tbl';
    protected $primaryKey   =   'student_id_pk';
    public $timestamps      =   false;

    protected $guarded = [];

    public function institute()
    {
        return $this->hasOne('App\Models\wbscte\Institute', "inst_sl_pk", "student_inst_id")->withDefault(function () {
            return new Institute();
        });
    }
    public function role()
    {
        return $this->hasOne('App\Models\wbscte\Role', "role_id", "u_role_id")->withDefault(function () {
            return new Role();
        });
    }
    public function course()
    {
        return $this->hasOne('App\Models\wbscte\Course', "course_id_pk", "student_course_id")->withDefault(function () {
            return new Course();
        });
    }
    public function state()
    {
        return $this->hasOne('App\Models\wbscte\State', "state_id_pk", "student_state")->withDefault(function () {
            return new State();
        });
    }
    public function district()
    {
        return $this->hasOne('App\Models\wbscte\District', "district_id_pk", "student_district")->withDefault(function () {
            return new District();
        });
    }
    public function subdivision()
    {
        return $this->hasOne('App\Models\wbscte\Subdivision', "id", "student_subdivision")->withDefault(function () {
            return new Subdivision();
        });
    }
    public function enrollment()
    {
        return $this->hasOne('App\Models\wbscte\Enrollment', "reg_no", "student_reg_no")->withDefault(function () {
            return new Enrollment();
        });
    }
    public function roll()
    {
        return $this->hasOne('App\Models\wbscte\ExamRoll', "reg_no", "student_reg_no")->withDefault(function () {
            return new ExamRoll();
        });
    }
}
