<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = ['year_name', 'start_date', 'end_date'];

    public function courseStudents()
    {
        return $this->hasMany(CourseStudent::class);
    }

    public function courseLecturers()
    {
        return $this->hasMany(CourseLecturer::class);
    }
}
