<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseScheduleDetail extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'course_schedule_id',
        'activity_id',
        'total_sessions',
    ];

    // Relasi ke block schedule
    public function blockSchedule()
    {
        return $this->belongsTo(CourseSchedule::class);
    }

    // Relasi ke jenis kegiatan
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
