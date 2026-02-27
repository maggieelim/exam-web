<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamCredential extends Model
{
    use HasFactory;
    protected $fillable = [
        'exam_id',
        'username',
        'password',
        'plain_password',
        'nim',
        'is_used',
        'used_at',
        'created_at',
        'updated_at',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'nim');
    }
}
