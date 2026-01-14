<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicalRotation extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function hospitalRotations()
    {
        return $this->hasMany(HospitalRotation::class);
    }
}
