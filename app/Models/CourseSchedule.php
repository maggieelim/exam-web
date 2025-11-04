<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSchedule extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['kelompok', 'semester_id', 'course_id', 'year_level', 'created_by'];

    // Relasi ke semester
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    // Relasi ke tahun akademik
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Relasi ke blok (course)
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relasi ke user pembuat (kaprodi)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke detail jumlah kegiatan
    public function details()
    {
        return $this->hasMany(CourseScheduleDetail::class);
    }
    public function practicumGroups()
    {
        return $this->hasMany(PracticumGroup::class, 'course_schedule_id');
    }
}
