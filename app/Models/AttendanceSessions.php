<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->hasMany(AttendanceRecords::class, 'attendance_session_id');
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
    public function getFormattedScheduleAttribute()
    {
        if (!$this->teachingSchedule) {
            return '-';
        }

        $date = Carbon::parse($this->teachingSchedule->scheduled_date)->format('D d/M');
        $start = Carbon::parse($this->teachingSchedule->start_time)->format('H:i');
        $end = Carbon::parse($this->teachingSchedule->end_time)->format('H:i');

        return "$date $start-$end";
    }

    public function getFormatted1ScheduleAttribute()
    {
        if (!$this) {
            return '-';
        }
        $date = Carbon::parse($this->start_time)->format('l, d F Y');
        $start = Carbon::parse($this->start_time)->format('H:i');
        $end = Carbon::parse($this->end_time)->format('H:i');

        return "$date $start - $end";
    }

    public function getStudentFormattedTimeAttribute()
    {
        if (!$this) {
            return '-';
        }
        $date = Carbon::parse($this->start_time)->format('l, d F Y');
        $start = Carbon::parse($this->start_time)->format('H:i');
        $end = Carbon::parse($this->end_time)->format('H:i');

        return "$date $start - $end";
    }
}
