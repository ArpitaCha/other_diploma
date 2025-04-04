<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterFees extends Model
{
    // use HasFactory;
    protected $table        =   'wbscte_od_register_fees';
    protected $primaryKey   =   'rf_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
