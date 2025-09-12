<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',

    ];


    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_students', 'user_id', 'course_id');
    }

    public function lecturerCourses()
    {
        return $this->belongsToMany(Course::class, 'course_lecturer', 'lecturer_id', 'course_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function lecturer()
    {
        return $this->hasOne(Lecturer::class, 'user_id');
    }

    public function examAnswers()
    {
        return $this->hasMany(ExamAnswer::class);
    }
}
