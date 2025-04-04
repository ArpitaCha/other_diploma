<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    use HasFactory;

    protected $table        =   'wbscte_other_diploma_paper_master';
    protected $primaryKey   =   'paper_id_pk';
    public $timestamps      =   false;

    protected $guarded = [];
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
