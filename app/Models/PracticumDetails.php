<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticumDetails extends Model
{
    use HasFactory;

    protected $table = 'practicum_details';
    protected $fillable = [
        'teaching_schedule_id',
        'practicum_group_id',
        'lecturer_id',
    ];

    // Dosen milik satu grup praktikum
    public function group()
    {
        return $this->belongsTo(PracticumGroup::class, 'practicum_group_id');
    }
    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }
    public function teachingSchedule()
    {
        return $this->belongsTo(TeachingSchedule::class, 'teaching_schedule_id');
    }
}
