<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LecturerKoas extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
    ];
    protected $fillable = [
        'lecturer_id',
        'hospital_rotation_id',
        'created_at',
    ];

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }
    public function hospitalRotation()
    {
        return $this->belongsTo(HospitalRotation::class);
    }
}
