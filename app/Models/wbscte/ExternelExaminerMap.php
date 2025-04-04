<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternelExaminerMap extends Model
{
    use HasFactory;

    protected $table        =   'wbscte_external_examinner_mapping_master_tbl';
    protected $primaryKey   =   'map_id';
    public $timestamps      =   false;

    protected $guarded = [];

    public function internelInstituteName()
    {
        return $this->hasOne('App\Models\wbscte\Institute', "inst_sl_pk", "map_source_inst_id")->withDefault(function () {
            return new Institute();
        });
    }

    public function ExternerInstituteName()
    {
        return $this->hasOne('App\Models\wbscte\Institute', "inst_sl_pk", "map_assign_inst_id")->withDefault(function () {
            return new Institute();
        });
    }

    public function course()
    {
        return $this->hasOne('App\Models\wbscte\Course', "course_id_pk", "map_course_id")->withDefault(function () {
            return new Course();
        });
    }

    public function paper()
    {
        return $this->hasOne('App\Models\wbscte\Paper', "paper_id_pk", "map_paper_id")->withDefault(function () {
            return new Paper();
        });
    }
    public function user()
    {
        return $this->hasOne('App\Models\wbscte\User', "u_id", "map_examiner_id")->where('is_active', 1)->withDefault(function () {
            return new User();
        });
    }
}
