<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nim',
        'type',
        'gender',
        'angkatan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_students')
            ->withPivot('semester_id')
            ->withTimestamps();
    }
    public function courseStudents()
    {
        return $this->hasMany(CourseStudent::class);
    }
}
