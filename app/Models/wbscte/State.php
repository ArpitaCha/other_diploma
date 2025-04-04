<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Model;
use DB;
use Session;

class State extends Model
{
    protected $table        =   'wbscte_other_diploma_state_master';
    protected $primaryKey   =   'state_id_pk';
    public $timestamps      =   false;

    protected $fillable = [
        'state_name', 'active_status'
    ];
}
