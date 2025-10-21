<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_id',
        'status',
        'finished_at',
        'started_at',
        'question_order',
    ];
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
    public function answers()
    {
        return $this->hasMany(ExamAnswer::class, 'exam_id', 'exam_id')
            ->where('user_id', $this->user_id);
    }
       public function findAnswerByQuestion($questionId)
    {
        return ExamAnswer::where('exam_id', $this->exam_id)
                        ->where('user_id', $this->user_id)
                        ->where('exam_question_id', $questionId)
                        ->first();
    }
}
