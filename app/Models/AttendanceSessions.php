<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSessions extends Model
{
    use HasFactory;

    protected $fillable = ['loc_name', 'teaching_schedule_id', 'semester_id', 'course_id', 'activity_id', 'absensi_code', 'start_time', 'end_time', 'location_lat', 'location_long', 'tolerance_meter', 'status', 'created_at', 'updated_at'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function token()
    {
        return $this->hasMany(AttendanceTokens::class, 'attendance_session_id');
    }

    public function studentRecords()
    {
        return $this->hasMany(AttendanceRecords::class);
    }

    public function lecturerRecords()
    {
        return $this->hasMany(LecturerAttendanceRecords::class, 'attendance_session_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function isExpired()
    {
        return $this->end_time && Carbon::now()->greaterThan($this->end_time);
    }

    public function isActive()
    {
        return $this->start_time && Carbon::now()->between($this->start_time, $this->end_time);
    }
    public function updateStatusIfExpired()
    {
        if ($this->status !== 'finished' && Carbon::now()->gt(Carbon::parse($this->end_time))) {
            $this->update(['status' => 'finished']);
        }
    }

    public function teachingSchedule()
    {
        return $this->belongsTo(TeachingSchedule::class, 'teaching_schedule_id');
    }
}
