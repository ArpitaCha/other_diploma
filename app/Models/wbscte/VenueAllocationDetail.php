<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueAllocationDetail extends Model
{
    use HasFactory;
    protected $table        =   'venue_allocation_details';
    protected $primaryKey   =   'id';
    public $timestamps      =   false;

    protected $guarded = [];
}
