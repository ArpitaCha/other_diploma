<?php

namespace App\Models\wbscte;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fees extends Model
{
    use HasFactory;

    protected $table = 'od_payment_fees';
    public $timestamps = false;

    protected $guarded = [];

    // table update
    // public function updatediscipline()
    // {
    //     return $this->hasOne(Discipline::class, "id", "discipline_code");
    // }
}