<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = ['name', 'academic_year_id'];

    public function courseStudents()
    {
        return $this->hasMany(CourseStudent::class);
    }

    public function courseLecturers()
    {
        return $this->hasMany(CourseLecturer::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
