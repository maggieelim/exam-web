<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nim',
        'tahun_ajaran',
        'jurusan',
        'kelas',
        'angkatan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
