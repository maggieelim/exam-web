<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HospitalRotation extends Model
{
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $fillable = [
        'hospital_id',
        'clinical_rotation_id',
        'start_date',
        'end_date',
        'semester_id',
        'created_at',
        'updated_at'
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function clinicalRotation()
    {
        return $this->belongsTo(ClinicalRotation::class);
    }
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
    public function studentKoas()
    {
        return $this->hasMany(StudentKoas::class, 'hospital_rotation_id');
    }
}
