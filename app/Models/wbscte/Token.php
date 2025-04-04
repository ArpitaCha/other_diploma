<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Model;
use DB;
use Session;

class Token extends Model
{
    protected $table        =   'wbscte_other_diploma_tokens';
    protected $primaryKey   =   't_token';
    public $timestamps      =   false;

    protected $fillable = [
        't_token', 't_generated_on', 't_expired_on', 't_user_id'
    ];
}
