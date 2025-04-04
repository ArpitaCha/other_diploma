<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    

    protected $table        =   'wbscte_other_diploma_dashboard_notification ';
    protected $primaryKey   =   'noti_id';
    public $timestamps      =   false;

    protected $guarded = [];
}
