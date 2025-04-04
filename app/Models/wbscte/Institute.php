<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    protected $table        =   'wbscte_other_diploma_institute_master';
    protected $primaryKey   =   'inst_sl_pk';
    public $timestamps      =   false;

    protected $fillable = [
        'institute_code', 'institute_name', 'institute_address', 'is_active'
    ];
}
