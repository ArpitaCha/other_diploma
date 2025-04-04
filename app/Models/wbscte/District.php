<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Model;
use DB;
use Session;

class District extends Model
{
    protected $table        =   'wbscte_other_diploma_district_master';
    protected $primaryKey   =   'district_id_pk';
    public $timestamps      =   false;

    protected $fillable = [
        'state_id_fk', 'district_name', 'district_name', 'active_status', 'schcd', 'google_district_name', 'lgd_code'
    ];

    public function state()
    {
        return $this->hasOne('App\Models\wbscte\State', "state_id_pk", "state_id_fk")->withDefault(function () {
            return new State();
        });
    }
}
