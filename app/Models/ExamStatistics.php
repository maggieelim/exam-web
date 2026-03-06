<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamStatistics extends Model
{
    use HasFactory;
    protected $fillable = [
        'exam_id',
        'exam_question_id',
        'total_students',
        'correct_count',
        'correct_percentage',
        'discrimination_index',
        'difficulty_level',
        'options_summary',
    ];

    protected $casts = [
        'options_summary' => 'array',
    ];

    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }
}
