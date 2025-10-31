<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseCoordinator extends Model
{
    protected $fillable = ['course_id', 'lecturer_id', 'semester_id'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
