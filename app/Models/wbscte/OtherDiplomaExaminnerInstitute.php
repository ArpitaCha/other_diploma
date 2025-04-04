<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherDiplomaExaminnerInstitute extends Model
{
    use HasFactory;
    protected $table        =   'wbscte_other_diploma_examiner_institute_tag_master';
    protected $primaryKey   =   'examiner_id';
    public $timestamps      =   false;
    protected $fillable = [
        "examiner_name",    "examiner_phone",    "examiner_email",    "examiner_inst_id",    "examiner_inst_code",    "examiner_user_id", "is_active", "examiner_paper_id", "examiner_course_id", "examiner_part_sem", "examiner_bank_account_holder_name", "examiner_bank_account_no", "examiner_bank_ifsc", "examiner_bank_branch_name", "map_paper_entry_type", "map_paper_type",'assign_status'
    ];

    public function institute()
    {
        return $this->hasOne('App\Models\wbscte\Institute', "inst_sl_pk", "examiner_inst_id")->where('is_active', 1)->withDefault(function () {
            return new Institute();
        });
    }
    public function course()
    {
        return $this->hasOne('App\Models\wbscte\Course', "course_id_pk", "examiner_course_id")->withDefault(function () {
            return new Course();
        });
    }
    public function paper()
    {
        return $this->hasOne('App\Models\wbscte\TheorySubject', "paper_id_pk", "examiner_paper_id")->withDefault(function () {
            return new TheorySubject();
        });
    }

    public function user()
    {
        return $this->hasOne('App\Models\wbscte\User', "u_id", "examiner_user_id")->withDefault(function () {
            return new User();
        });
    }
    public function courses()
    {
        return $this->hasMany('App\Models\wbscte\Course', "inst_id", "examiner_inst_id")->where('is_active', 1)->select('course_id_pk','course_code','course_name','inst_id');
    }
}
