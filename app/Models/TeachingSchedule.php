<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['course_schedule_id', 'course_id', 'semester_id', 'activity_id', 'session_number', 'lecturer_id', 'scheduled_date', 'start_time', 'end_time', 'room',"group", 'topic', 'created_by', 'updated_at', 'zone'];

    /**
     * Relasi ke tabel Course (blok/mata kuliah)
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relasi ke tabel Semester
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Relasi ke tabel Activity (jenis kegiatan)
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function courseSchedule()
    {
        return $this->belongsTo(CourseSchedule::class);
    }
    /**
     * Relasi ke tabel Lecturer (dosen pengampu)
     */
    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    /**
     * Relasi ke user yang membuat jadwal (biasanya kaprodi)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: hanya jadwal milik dosen tertentu
     */
    public function scopeForLecturer($query, $lecturerId)
    {
        return $query->where('lecturer_id', $lecturerId);
    }

    /**
     * Scope: hanya jadwal di semester tertentu
     */
    public function scopeForSemester($query, $semesterId)
    {
        return $query->where('semester_id', $semesterId);
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
}
