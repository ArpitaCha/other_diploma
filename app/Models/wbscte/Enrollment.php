<?php

 namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollment_details';
    public $timestamps = false;

    protected $guarded = [];
    public function student()
    {
        return $this->hasOne('App\Models\wbscte\Student', 'student_reg_no', 'reg_no');
    }

   
}