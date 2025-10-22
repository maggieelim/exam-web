<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecords extends Model
{
    use HasFactory;
    protected $fillable = ['attendance_session_id', 'course_student_id', 'nim', 'latitude', 'longitude', 'wifi_ssid', 'device_info', 'scanned_at', 'status', 'created_at', 'updated_at'];

    public function courseStudent(){
        return $this->belongsTo(CourseStudent::class);
    }

    public function session(){
        return $this->belongsTo(AttendanceSessions::class, 'absensi_code', 'absensi_code');
    }
}
