<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'question_text',
        'question_type',
        'options',
        'answer',
    ];

    protected $casts = [
        'options' => 'array', // otomatis jadi array ketika ambil dari DB
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
    public function answers()
    {
        return $this->hasMany(ExamAnswer::class);
    }
}
