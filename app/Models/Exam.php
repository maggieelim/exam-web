<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_code',
        'title',
        'course_id',
        'exam_date',
        'room',
        'password',
        'duration',
        'created_by',
        'updated_by',
        'status',
        'is_published'
    ];

    protected $casts = [
        'exam_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($exam) {
            $lastExam = self::orderBy('id', 'desc')->first();
            $nextNumber = $lastExam ? ((int) substr($lastExam->exam_code, 4)) + 1 : 1;

            $exam->exam_code = 'EXM-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        });
    }

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

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class, 'exam_id');
    }
    public function answers()
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }
    public function userAttempt($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->hasOne(ExamAttempt::class)->where('user_id', $userId);
    }

    public function categories()
    {
        return $this->hasMany(ExamQuestionCategory::class);
    }
}
