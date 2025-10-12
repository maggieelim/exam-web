<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseStudent extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [
        'id',
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'user_id', 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
