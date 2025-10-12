<?php

namespace App\Exports;

use App\Models\CourseStudent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CourseStudentsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
  protected $course;
  protected $semesterId;

  public function __construct($course, $semesterId)
  {
    $this->course = $course;
    $this->semesterId = $semesterId;
  }

  public function collection()
  {
    return CourseStudent::with('student.user')
      ->where('course_id', $this->course->id)
      ->where('semester_id', $this->semesterId)
      ->get()
      ->map(function ($item) {
        return [
          'NIM'   => $item->student->nim,
          'Nama'  => $item->student->user->name,
          'Email' => $item->student->user->email,
          'Angkatan' => $item->student->angkatan
        ];
      });
  }

  public function headings(): array
  {
    return ['NIM', 'Nama', 'Email', 'Angkatan'];
  }

  public function title(): string
  {
    return 'Mahasiswa';
  }

  public function styles(Worksheet $sheet)
  {
    // Gaya untuk header baris pertama
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
      'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFFFF'], // putih
        'size' => 12,
      ],
      'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
      ],
      'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF5CB6ED'], // warna biru muda #5cb6ed
      ],
    ]);

    // Tambahkan border tipis untuk semua sel
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
      ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    return [];
  }
}
