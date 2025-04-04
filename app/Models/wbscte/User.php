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

    protected $fillable = [
        'u_ref', 'u_ct_id', 'u_username', 'u_password', 'u_fullname', 'u_phone', 'u_email', 'bank_account_holder_name', 'bank_account_no', 'bank_ifsc', 'bank_branch_name',
        'u_role_id', 'is_active', 'is_direct', 'created_at', 'updated_at','u_inst_id'
    ];

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
