<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemicuScore extends Model
{
    use HasFactory;

    protected $table = 'pemicu_scores';

    protected $fillable = [
        'pemicu_detail_id',
        'teaching_schedule_id',
        'course_student_id',
        'disiplin',
        'keaktifan',
        'berpikir_kritis',
        'info_baru',
        'analisis_rumusan',
        'total_score',
    ];


    public function pemicuDetail()
    {
        return $this->belongsTo(PemicuDetails::class, 'pemicu_detail_id');
    }


    public function teachingSchedule()
    {
        return $this->belongsTo(TeachingSchedule::class, 'teaching_schedule_id');
    }



    public function courseStudent()
    {
        return $this->belongsTo(CourseStudent::class, 'course_student_id');
    }
}
