<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table        =   'wbscte_other_diploma_attendence_tbl';
    protected $primaryKey   =   'att_id';
    public $timestamps      =   false;

    protected $fillable = [
        'att_reg_no', 'att_inst_id', 'att_course_id', 'att_paper_id', 'att_sem', 'att_paper_type', 'att_is_present', 'att_is_absent', 'att_is_ra', 'attr_sessional_yr', 'is_final_submit', 'att_paper_entry_type', 'att_created_by', 'att_created_on', 'att_modified_on', 'att_modified_by','is_lock'
    ];

    public function institute()
    {
        return $this->hasOne('App\Models\wbscte\Institute', "inst_sl_pk", "att_inst_id")->withDefault(function () {
            return new Institute();
        });
    }

    public function student()
    {
        return $this->hasOne('App\Models\wbscte\Student', "student_reg_no", "att_reg_no")->withDefault(function () {
            return new Student();
        });
    }

    public function marks()
    {
        return $this->hasOne('App\Models\wbscte\MarksEntry', "att_id", "att_id")->withDefault(function () {
            return new MarksEntry();
        });
    }

    public function paperMarks()
    {
        return $this->hasOne('App\Models\wbscte\Paper', "paper_id_pk", "att_paper_id")->withDefault(function () {
            return new Paper();
        });
    }
}
