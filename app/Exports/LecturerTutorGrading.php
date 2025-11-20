<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class LecturerTutorGrading implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected $course;
    protected $students;
    protected $kelompok;
    protected $pemicuCount;

    public function __construct($course, Collection $students, $kelompok)
    {
        $this->course = $course;
        $this->students = $students;
        $this->kelompok = $kelompok;
        $this->calculatePemicuCount();
    }

    protected function calculatePemicuCount()
    {
        if ($this->students->isEmpty()) {
            $this->pemicuCount = 0;
            return;
        }

        $firstStudent = $this->students->first();
        $this->pemicuCount = $firstStudent
            ->pluck('pemicuDetail.teachingSchedule.pemicu_ke')
            ->map(function ($val) {
                return substr($val, 0, 1); // ambil angka pertama
            })
            ->unique()
            ->count();
    }

    public function headings(): array
    {
        // Header baris 1
        $header1 = ['KEL', 'NO', 'NIM', 'NAMA'];

        // Tambahkan kolom untuk setiap pemicu
        for ($i = 1; $i <= $this->pemicuCount; $i++) {
            $header1[] = 'Pemicu ' . $i;
            $header1[] = ''; // Kolom kosong untuk merge
        }

        // Header baris 2
        $header2 = ['KEL', 'NO', 'NIM', 'NAMA'];
        for ($i = 1; $i <= $this->pemicuCount; $i++) {
            $header2[] = 'Nilai';
            $header2[] = '%';
        }

        return [$header1, $header2];
    }

    public function array(): array
    {
        $output = [];
        $no = 1;

        foreach ($this->students as $courseStudentId => $scores) {
            $first = $scores->first();

            if (!$first || !$first->courseStudent || !$first->courseStudent->student) {
                continue;
            }

            $student = $first->courseStudent->student;
            $totalScore = $scores->sum('total_score');
            $percentage = ($totalScore / 24) * 100;

            $row = [
                $this->kelompok,
                $no++,
                $student->nim,
                $student->user->name,
                $totalScore,
                $percentage,
            ];

            $output[] = $row;
        }

        return $output;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            2 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            // Style untuk data
            'A:B' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            'C:C' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            'E:E' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                // Merge cells untuk header baris 1
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:D2');
                $sheet->mergeCells("A3:A{$highestRow}");

                // Merge cells untuk setiap pemicu
                $startCol = 5; // Kolom E

                for ($i = 1; $i <= $this->pemicuCount; $i++) {
                    $col1 = Coordinate::stringFromColumnIndex($startCol);
                    $col2 = Coordinate::stringFromColumnIndex($startCol + 1);

                    $sheet->mergeCells($col1 . '1:' . $col2 . '1');

                    $startCol += 2;
                }

                // Set alignment untuk header yang di-merge
                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Auto size columns
                foreach (range('A', $sheet->getHighestColumn()) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Add borders
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1:' . $lastColumn . $lastRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Style untuk persentase (warna berbeda)
                $startDataCol = 6; // Kolom F (persentase dimulai dari kolom genap)
                for ($i = 1; $i <= $this->pemicuCount; $i++) {
                    $percentCol = Coordinate::stringFromColumnIndex($startDataCol);
                    $sheet->getStyle($percentCol . '3:' . $percentCol . $lastRow)
                        ->getNumberFormat()
                        ->setFormatCode('0.00"%"');
                    $startDataCol += 2;
                }
            },
        ];
    }
}
