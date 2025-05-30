<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTwo extends Model
{
    use HasFactory;
     protected $table        =   'users_table_two';
    protected $primaryKey   =   'id';
    public $timestamps      =   false;
      protected $fillable = [
        'id', 'username', 'phone_no', 'u_role_id', 'u_inst_id', 'email'
    ];


}
