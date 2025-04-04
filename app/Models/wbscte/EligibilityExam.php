<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EligibilityExam extends Model
{
    // use HasFactory;
    protected $table        =   'appl_elgb_exam';
    protected $primaryKey   =   'id';
    public $timestamps      =   false;

    protected $guarded = [];
}
