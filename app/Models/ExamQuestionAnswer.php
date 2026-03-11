<?php

namespace App\Models;

use App\Http\Controllers\PSSK\ExamStatisticsController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestionAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_question_id',
        'option',
        'text',
        'image',
        'is_correct',
    ];

    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }
    protected static function booted()
    {
        parent::boot();
        static::saved(function ($answer) {
            $question = $answer->question;
            if ($answer->wasChanged('is_correct') || $answer->wasChanged('text')) {
                app(ExamStatisticsController::class)
                    ->regenerateQuestion($question->exam, $question);
            }
        });
    }
}
