<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamRoll extends Model
{
    use HasFactory;
    protected $table = 'exam_roll_no';
    public $timestamps = false;

    protected $guarded = [];
}
