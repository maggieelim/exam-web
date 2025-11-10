<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'bagian', 'nidn', 'faculty', 'gender', 'strata', 'gelar', 'tipe_dosen', 'min_sks', 'max_sks'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lecturer) {
            if (empty($lecturer->faculty)) {
                $lecturer->faculty = 'Kedokteran';
            }
        });
    }
    public function coordinatedCourses()
    {
        return $this->hasMany(CourseCoordinator::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courseLecturers()
    {
        return $this->hasMany(CourseLecturer::class, 'lecturer_id');
    }
}
