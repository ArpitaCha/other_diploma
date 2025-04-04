<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigFees extends Model
{
    // use HasFactory;
    protected $table        =   'od_config_fees';
    protected $primaryKey   =   'cf_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
