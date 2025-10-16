<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CoursesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $courses;
    protected $semesterId;

    public function __construct($courses, $semesterId)
    {
        $this->courses = $courses;
        $this->semesterId = $semesterId;
    }

    public function collection()
    {
        return $this->courses;
    }

    public function headings(): array
    {
        return [
            'Kode Blok',
            'Nama',
            'Semester',
            'Total Dosen',
            'Total Mahasiswa',
        ];
    }

    public function map($course): array
    {
        return [
            $course->kode_blok,
            $course->name,
            $course->semester,
            number_format($course->lecturers->count() ?? 0),
            number_format($course->students->count() ?? 0),
        ];
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
