<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticumLecturer extends Model
{
    use HasFactory;

    protected $table = 'practicum_lecturers';
    protected $fillable = [
        'practicum_group_id',
        'lecturer_id',
    ];

    // Dosen milik satu grup praktikum
    public function group()
    {
        return $this->belongsTo(PracticumGroup::class, 'practicum_group_id');
    }

    // Relasi ke tabel dosen
    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }
}
