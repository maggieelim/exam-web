<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseLecturer extends Model
{
    protected $table = 'course_lecturer'; // atau 'course_lecturer' sesuai database

    protected $fillable = ["semester_id","lecturer_id", "course_id", "created_at", "updated_at"];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    
    public function attendance(){
        return $this->hasMany(LecturerAttendanceRecords::class);
    }

    public function activities(){
        return $this->hasMany(CourseLecturerActivity::class);
    }
    public function hasActivity($activityName)
    {
        return $this->activities->contains(function ($activity) use ($activityName) {
            return strtolower($activity->activity->activity_name) === strtolower($activityName);
        });
    }
}
