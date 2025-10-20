<?php

namespace App\Imports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use Exception;

class CoursesImport implements ToModel, WithHeadingRow
{
    private $processed = [];
    private $rowNumber = 1; // untuk melacak baris

    public function model(array $row)
    {
        $this->rowNumber++;

        $kodeBlok = $row['kode_blok'] ?? null;
        $nama = $row['nama_blok'] ?? null;
        $semester = $row['semester'] ?? null;

        // Lewati jika kode_blok kosong atau sudah pernah diproses
        if (empty($kodeBlok) || in_array($kodeBlok, $this->processed)) {
            return null;
        }

        $this->processed[] = $kodeBlok;

        // Lewati jika nama kosong
        if (empty($nama)) {
            return null;
        }

        // Validasi semester
        $allowedSemesters = ['Ganjil', 'Genap', 'Ganjil/Genap'];
        if (!in_array($semester, $allowedSemesters)) {
            throw new Exception("Semester invalid pada blok {$nama}: '{$semester}'. Hanya boleh Ganjil, Genap, atau Ganjil/Genap.");
        }

        if(Course::where('kode_blok', $row['kode_blok'])->exists()){
          throw new Exception("Kode Blok {$row['kode_blok']} sudah terdaftar");
        }
        
        return new Course([
            'kode_blok' => $kodeBlok,
            'name'      => $nama,
            'slug'      => Str::slug($nama),
            'semester'  => $semester,
        ]);
    }
}
