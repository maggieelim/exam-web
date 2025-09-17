<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    protected $table = 'soals';

    protected $fillable = [
        'badan_soal',
        'kalimat_tanya',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
        'jawaban',
        'kode_soal',
        'nama_blok',
        'jadwal_ujian_id',
    ];

    public function jadwal()
{
    return $this->belongsTo(JadwalUjian::class, 'jadwal_ujian_id');
}

}
