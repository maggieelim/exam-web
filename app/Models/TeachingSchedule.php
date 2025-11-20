<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingSchedule extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['course_schedule_id', 'course_id', 'semester_id', 'activity_id', 'pemicu_ke', 'session_number', 'lecturer_id', 'scheduled_date', 'start_time', 'end_time', 'room', "group", 'topic', 'created_by', 'updated_at', 'zone'];

    public function clearSchedule()
    {
        return $this->update([
            'scheduled_date' => null,
            'start_time' => null,
            'end_time' => null,
            'room' => null,
            'zone' => null,
            'group' => null,
            'topic' => null,
            'lecturer_id' => null,
        ]);
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function courseSchedule()
    {
        return $this->belongsTo(CourseSchedule::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForLecturer($query, $lecturerId)
    {
        return $query->where('lecturer_id', $lecturerId);
    }

    public function scopeForSemester($query, $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }
    public function examDetail()
    {
        return $this->hasOne(Exam::class, 'teaching_schedule_id');
    }
    public function skillslabDetails()
    {
        return $this->hasMany(SkillslabDetails::class);
    }
    public function practicumDetails()
    {
        return $this->hasMany(PracticumDetails::class);
    }
    public function pemicuDetails()
    {
        return $this->hasMany(PemicuDetails::class);
    }
    public function plenoDetails()
    {
        return $this->hasMany(PlenoDetails::class);
    }
    public function practicumGroups()
    {
        return $this->hasMany(PracticumGroup::class, 'teaching_schedule_id');
    }

    public function attendance()
    {
        return $this->hasMany(AttendanceSessions::class, 'teaching_schedule_id');
    }
    public function getPemicuAttribute()
    {
        return $this->pemicu_ke ?? ceil($this->session_number / 2);
    }
    public function pemicuScores()
    {
        return $this->hasMany(PemicuScore::class);
    }

    public function scopeForPemicu($query, $pemicuNumber)
    {
        return $query->where('pemicu_ke', $pemicuNumber)
            ->orWhereRaw('CEIL(session_number / 2) = ?', [$pemicuNumber]);
    }
}
