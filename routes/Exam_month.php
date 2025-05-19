<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamMonth extends Model
{
    protected $table        =   'exam_sem_month';
    protected $primaryKey   =   'id';
    public $timestamps      =   false;

    protected $guarded = [];
}
?>