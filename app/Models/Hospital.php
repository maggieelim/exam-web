<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public function hospitalRotations()
    {
        return $this->hasMany(HospitalRotation::class);
    }
}
