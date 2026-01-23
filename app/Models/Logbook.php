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
        'note'
    ];

    protected $casts = [
        'date' => 'date',
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
