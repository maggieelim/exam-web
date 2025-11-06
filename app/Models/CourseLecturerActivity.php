<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLecturerActivity extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['course_lecturer_id', 'activity_id'];

    public function activity(){
        return $this->belongsTo(Activity::class);
    }

   public function courseLecturer()
    {
        return $this->belongsTo(CourseLecturer::class);
    }

    public function lecturer()
    {
        // lewat CourseLecturer → Lecturer
        return $this->hasOneThrough(
            Lecturer::class,
            CourseLecturer::class,
            'id', // Foreign key di CourseLecturer (primary key)
            'id', // Foreign key di Lecturer (primary key)
            'course_lecturer_id', // Foreign key di CourseLecturerActivity
            'lecturer_id' // Foreign key di CourseLecturer
        );
    }
}
