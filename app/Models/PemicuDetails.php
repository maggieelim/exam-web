<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemicuDetails extends Model
{
    use HasFactory;
    protected $table = 'pemicu_details';
    protected $fillable = [
        'teaching_schedule_id',
        'kelompok_num',
        'lecturer_id',
    ];
    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }
    public function teachingSchedule()
    {
        return $this->belongsTo(TeachingSchedule::class, 'teaching_schedule_id');
    }
    public function getPemicuNumberAttribute()
    {
        // session_number ada di teachingSchedule
        return ceil($this->teachingSchedule->session_number / 2);
    }
}
