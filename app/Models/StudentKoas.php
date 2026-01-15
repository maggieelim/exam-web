<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentKoas extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    protected $fillable = [
        'student_id',
        'hospital_rotation_id',
        'semester_id',
        'status',
        'start_date',
        'end_date',
        'created_at',
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
