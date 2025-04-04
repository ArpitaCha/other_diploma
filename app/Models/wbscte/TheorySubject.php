<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TheorySubject extends Model
{
    use HasFactory;
    protected $table        =   'wbscte_other_diploma_paper_master';
    protected $primaryKey   =   'paper_id_pk';
    public $timestamps      =   false;
    protected $fillable = [
        "course_id",    "paper_code",    "paper_name",    "inst_id",    "paper_semester",    "paper_type", "paper_category",   "paper_credit",    "paper_full_marks",    "paper_internal_marks", "paper_external_marks", "paper_sessional_theory_marks", "paper_sessional_practical_marks",    "paper_pass_marks",    "paper_affiliation_year",    "is_active",
    ];

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
}
