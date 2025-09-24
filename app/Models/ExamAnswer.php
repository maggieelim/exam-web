<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['exam_id', 'exam_question_id', 'user_id', 'answer', 'marked_doubt', 'is_correct', 'score'];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function selectedOption()
    {
        return $this->belongsTo(ExamQuestionAnswer::class, 'answer');
    }
}
