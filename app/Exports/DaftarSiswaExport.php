<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\CourseStudent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DaftarSiswaExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $courseId;
    protected $semesterId;
    protected $courseName;

    public function __construct($courseId, $semesterId)
    {
        $this->courseId = $courseId;
        $this->semesterId = $semesterId;
        $this->courseName = Course::where('id', $courseId)->value('name');
    }

    public function collection()
    {
        $students = CourseStudent::with(['student.user'])
            ->where('course_id', $this->courseId)
            ->where('semester_id', $this->semesterId)
            ->get();

        // Kelompokkan siswa berdasarkan 'kelompok'
        $grouped = $students->groupBy('kelompok')->sortKeys();

        $data = collect();

        foreach ($grouped as $kelompok => $items) {
            // Tambah baris header kelompok
            $data->push([
                'NIM' => "Kelompok: {$kelompok}  (Jumlah={$items->count()} Siswa)",
                'Nama' => '',
                'Gender' => '',

            ]);

            // Tambah baris siswa dalam kelompok
            foreach ($items as $student) {
                $data->push([
                    'NIM' => $student->student->nim ?? '-',
                    'Nama' => $student->student->user->name ?? '-',
                    'Gender' => $student->student->user->gender ?? '-',

                ]);
            }
        }

        // Tambahkan total siswa di akhir
        $data->push([
            'NIM' => "Total={$students->count()} Siswa",
            'Nama' => '',
            'Gender' => '',

        ]);

        return $data;
    }

    public function headings(): array
    {
        return ['NIM', 'Nama', 'Gender'];
    }

    public function title(): string
    {
        return 'Daftar Siswa';
    }

    public function styles(Worksheet $sheet)
    {
        // Border tipis untuk seluruh data siswa
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 5) {
            $sheet->getStyle("A5:C{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin',
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 35,
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // Tambahkan header informasi
                $event->sheet->setCellValue('A1', 'FK TARUMANAGARA - PENJADWALAN');
                $event->sheet->setCellValue('A2', 'PENJADWALAN PSSK - BLOK ' . ($this->courseName));
                $event->sheet->setCellValue('A3', 'Exported On ' . date('d M Y') . ' at ' . date('H:i:s'));
                $event->sheet->setCellValue('A4', '');

                // Merge cells untuk header informasi
                $event->sheet->mergeCells('A1:C1');
                $event->sheet->mergeCells('A2:C2');
                $event->sheet->mergeCells('A3:C3');
                $event->sheet->mergeCells('A4:C4');
            },

            AfterSheet::class => function (AfterSheet $event) {
                // Style untuk header informasi
                $event->sheet->getStyle('A1:A4')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => 'left',
                    ],
                ]);

                // Gaya untuk header kolom (row 5)
                $event->sheet->getStyle('A5:C5')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'c5e3f7'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                // Apply styles untuk baris kelompok dan merge cells
                $highestRow = $event->sheet->getHighestRow();
                $currentRow = 6; // Mulai dari row 6 setelah header

                // Cari semua baris yang berisi teks "Kelompok:" atau "Total="
                for ($row = 6; $row <= $highestRow; $row++) {
                    $cellValue = $event->sheet->getCell("A{$row}")->getValue();
                    if ($cellValue && (str_contains($cellValue, 'Kelompok:') || str_contains($cellValue, 'Total='))) {
                        // Merge cells untuk baris kelompok/total (A sampai D)
                        $event->sheet->mergeCells("A{$row}:C{$row}");

                        // Apply style untuk baris kelompok
                        $event->sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'c5e3f7'],
                            ],
                            'alignment' => [
                                'horizontal' => 'left',
                                'vertical' => 'center',
                            ],
                        ]);
                    }
                }

                // Style alignment untuk kolom
                $event->sheet->getStyle("A6:A{$highestRow}")->getAlignment()->setHorizontal('left');
                $event->sheet->getStyle("B6:B{$highestRow}")->getAlignment()->setHorizontal('left');
                $event->sheet->getStyle("C6:C{$highestRow}")->getAlignment()->setHorizontal('left');
            },
        ];
    }
}
