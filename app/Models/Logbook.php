<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_koas_id',
        'lecturer_id',
        'activity_koas_id',
        'date',
        'description',
        'file_path',
        'status',
        'note',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function studentKoas()
    {
        return $this->belongsTo(StudentKoas::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }
    public function activityKoas()
    {
        return $this->belongsTo(ActivityKoas::class);
    }
}
