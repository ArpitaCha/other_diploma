<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Model;
use DB;
use Session;

class User extends Model
{
    protected $table        =   'wbscte_other_diploma_users_master';
    protected $primaryKey   =   'u_id';
    public $timestamps      =   false;

   protected $guarded = [];

    public function role()
    {
        return $this->hasOne('App\Models\wbscte\Role', "role_id", "u_role_id")->withDefault(function () {
            return new Role();
        });
    }

    public function institute()
    {
        return $this->hasOne('App\Models\wbscte\Institute', "inst_sl_pk", "u_inst_id")->withDefault(function () {
            return new Institute();
        });
    }
}
