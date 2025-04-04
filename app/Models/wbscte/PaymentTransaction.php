<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $table        =   'od_payment_transaction';
    protected $primaryKey   =   'p_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
