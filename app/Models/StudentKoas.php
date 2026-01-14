<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentKoas extends Model
{
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    protected $fillable = [
        'student_id',
        'hospital_rotation_id',
        'semester_id',
        'start_date',
        'end_date'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function hospitalRotation()
    {
        return $this->belongsTo(HospitalRotation::class);
    }
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
