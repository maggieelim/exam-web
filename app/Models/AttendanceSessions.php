<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSessions extends Model
{
    use HasFactory;

    protected $fillable = ['semester_id', 'course', 'activity_type', 'absensi_code', 'start_time', 'end_time', 'location_lat', 'location_long', 'tolerance_meter', 'status', 'created_at', 'updated_at'];

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function semester(){
        return $this->belongsTo(Semester::class);
    }

    public function token(){
        return $this->hasMany(AttendanceTokens::class);
    }

    public function studentRecords(){
        return $this->hasMany(AttendanceRecords::class);
    }

    public function lecturerRecords(){
        return $this->hasMany(LecturerAttendanceRecords::class);
    }
}
