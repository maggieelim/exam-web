<?php

namespace App\Imports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class CoursesImport implements ToModel, WithHeadingRow
{
  private $processed = [];

  public function model(array $row)
  {
    $kodeBlok = $row['kode_blok'] ?? null;
    $nama = $row['nama_blok'] ?? null;
    $semester = $row['semester'] ?? null;

    // Lewati jika kode_blok kosong atau sudah pernah diproses
    if (empty($kodeBlok) || in_array($kodeBlok, $this->processed)) {
      return null;
    }

    // Tandai sudah diproses
    $this->processed[] = $kodeBlok;

    // Lewati jika nama kosong
    if (empty($nama)) {
      return null;
    }

    return new Course([
      'kode_blok' => $kodeBlok,
      'name'      => $nama,
      'slug'      => Str::slug($nama),
      'semester'      => $semester,
    ]);
  }
}
