<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticumGroup extends Model
{
    use HasFactory;

    protected $table = 'practicum_groups';
    protected $fillable = [
        'course_schedule_id',
        'teaching_schedule_id',
        'tipe',
        'group_code',
    ];

    // Grup punya banyak kelompok (member)
    public function members()
    {
        return $this->hasMany(PracticumGroupMember::class, 'practicum_group_id');
    }

    // Grup punya banyak dosen pengajar
    public function lecturers()
    {
        return $this->hasMany(PracticumLecturer::class, 'practicum_group_id');
    }

    // Grup terkait ke jadwal pengajaran
    public function teachingSchedule()
    {
        return $this->belongsTo(TeachingSchedule::class, 'teaching_schedule_id');
    }

    // Grup terkait ke jadwal mata kuliah
    public function courseSchedule()
    {
        return $this->belongsTo(CourseSchedule::class, 'course_schedule_id');
    }
}
