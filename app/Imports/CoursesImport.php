<?php

namespace App\Imports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class CoursesImport implements ToModel, WithHeadingRow
{
  public function model(array $row)
  {
    if (Course::where('kode_blok', $row['kode_blok'])->exists()) {
      throw new \Exception("Kode blok {$row['kode_blok']} sudah ada.");
    }

    if (empty($row['name'])) {
      throw new \Exception("Nama course tidak boleh kosong.");
    }

    $coverPath = isset($row['cover']) && $row['cover'] != ''
      ? $row['cover']
      : 'covers/default.png'; // pastikan file default.png ada di storage/app/public/covers

    return new Course([
      'kode_blok' => $row['kode_blok'],
      'name'      => $row['name'],
      'slug'      => Str::slug($row['name']),
      'cover'     => $coverPath,
    ]);
  }
}
