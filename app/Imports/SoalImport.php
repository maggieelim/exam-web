<?php

namespace App\Imports;

use App\Models\Soal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SoalImport implements ToModel, WithHeadingRow
{
    protected $kodeBatch;
    protected $namaBlok;
    protected $jadwalUjianId;

    public function __construct($kodeBatch, $namaBlok, $jadwalUjianId)
    {
        $this->kodeBatch = $kodeBatch;
        $this->namaBlok = $namaBlok;
        $this->jadwalUjianId = $jadwalUjianId;
    }

    public function model(array $row)
    {
        return new Soal([
            'badan_soal'    => $row['badan_soal'] ?? null,
            'kalimat_tanya' => $row['kalimat_tanya'] ?? null,
            'opsi_a'        => $row['a'] ?? null,
            'opsi_b'        => $row['b'] ?? null,
            'opsi_c'        => $row['c'] ?? null,
            'opsi_d'        => $row['d'] ?? null,
            'opsi_e'        => $row['e'] ?? null,
            'jawaban'       => strtoupper($row['jawaban'] ?? ''), // kalau ada kolom jawaban
            'kode_soal'     => $this->kodeBatch,
            'nama_blok'     => $this->namaBlok,
            'jadwal_ujian_id' => $this->jadwalUjianId,
        ]);
    }
}
