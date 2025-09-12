<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'exam_type_id', 'exam_date', 'room', 'duration'];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function examType()
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }
    public function questions()
    {
        return $this->hasMany(ExamQuestion::class, 'exam_id');
    }
    public function answers()
    {
        return $this->hasMany(ExamAnswer::class);
    }
}
