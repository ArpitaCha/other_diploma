<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;
    protected $table        =   'venue_allocation';
    protected $primaryKey   =   'id';
    public $timestamps      =   false;

    protected $guarded = [];
}
