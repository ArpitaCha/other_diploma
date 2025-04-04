<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CnfgMarks extends Model
{
    use HasFactory;
    
    protected $table        =   'wbscte_other_diploma_config_marks';
    protected $primaryKey   =   'id';
    public $timestamps      =   false;

    protected $guarded = [];
    public function institute()
    {
        return $this->hasOne('App\Models\wbscte\Institute', "inst_sl_pk", "config_for_inst_id")->withDefault(function () {
            return new Institute();
        });
    }
}
