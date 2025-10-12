<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
    ];
    protected $fillable = ['kode_blok', 'name', 'slug', 'cover'];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            $course->slug = Str::slug($course->name);
        });
    }

    // public function questions()
    // {
    //     return $this->hasMany(CourseQuestion::class, 'course_id', 'id');
    // }
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Course.php
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_students', 'course_id', 'user_id')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    public function lecturers()
    {
        return $this->belongsToMany(User::class, 'course_lecturer', 'course_id', 'lecturer_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'course_id');
    }
    public function courseStudents()
    {
        return $this->hasMany(CourseStudent::class);
    }
    public function courseLecturer()
    {
        return $this->hasMany(CourseLecturer::class);
    }
}
