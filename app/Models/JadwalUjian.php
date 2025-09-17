<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalUjian extends Model
{
    use HasFactory;

    protected $fillable = ['judul', 'tanggal', 'jam', 'durasi', 'ruangan'];

    public function soals()
    {
        return $this->hasMany(Soal::class, 'jadwal_ujian_id');
    }
}
