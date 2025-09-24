<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'badan_soal',
        'kalimat_tanya',
        'kode_soal',
        'created_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function options()
    {
        return $this->hasMany(ExamQuestionAnswer::class, 'exam_question_id');
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class);
    }
    public function isAnsweredBy($userId)
    {
        return $this->answers()->where('user_id', $userId)->exists();
    }

    // Cek apakah ditandai ragu-ragu oleh user
    public function isDoubtBy($userId)
    {
        return $this->answers()
            ->where('user_id', $userId)
            ->where('marked_doubt', true)
            ->exists();
    }
}
