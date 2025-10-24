<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LecturerAttendanceRecords extends Model
{
    use HasFactory;
    protected $fillable = ['attendance_session_id', 'course_lecturer_id', 'checked_in_at', 'status', 'created_at', 'updated_at'];

    public function courseLecturer(){
        return $this->belongsTo(CourseLecturer::class, 'course_lecturer_id');
    }

    public function session(){
        return $this->belongsTo(AttendanceSessions::class, 'attendance_session_id');
    }
}