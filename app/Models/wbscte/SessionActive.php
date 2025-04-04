<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Model;
use DB;
use Session;

class SessionActive extends Model
{
    protected $table        =   'wbscte_other_diploma_session_tbl';
    protected $primaryKey   =   'session_id_pk';
    public $timestamps      =   false;

    protected $guarded = [];
}

