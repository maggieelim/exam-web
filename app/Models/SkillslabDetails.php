<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillslabDetails extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['course_schedule_id','teaching_schedule_id', 'group_code', 'kelompok_num', 'lecturer_id'];

    public function teachingSchedule()
    {
        return $this->belongsTo(TeachingSchedule::class);
    }
    public function lecturer()
{
    return $this->belongsTo(Lecturer::class);
}
}
