<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // use HasFactory;
    protected $table        =   'od_payments';
    protected $primaryKey   =   'pay_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
