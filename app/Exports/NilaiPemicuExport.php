<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NilaiPemicuExport implements FromView, WithStyles, WithEvents
{
    public function __construct(
        public $groupedStudents,
        public $scores,
        public $groupLecturer,
        public $id1,
        public $id2,
        public $preGroup,
        public $course
    ) {}

    public function view(): View
    {
        return view('pssk.courses.pemicu.nilai_pemicu', [
            'groupedStudents' => $this->groupedStudents,
            'scores' => $this->scores,
            'groupLecturer' => $this->groupLecturer,
            'id1' => $this->id1,
            'id2' => $this->id2,
            'preGroup' => $this->preGroup,
            'course' => $this->course
        ]);
    }

    public function styles($sheet)
    {
        $centerAlignment = [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical'   => Alignment::VERTICAL_CENTER,
            'wrapText'   => true
        ];

        return [
            1 => ['alignment' => $centerAlignment],
            2 => ['alignment' => $centerAlignment],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Autofit untuk kolom tertentu
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('M')->setAutoSize(true);

                // Style untuk header
                $headerStyle = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9EAF7']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];

                $sheet->getStyle('A1:M2')->applyFromArray($headerStyle);

                // Border untuk seluruh tabel
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:M' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                ]);

                // Alignment untuk kolom C-L
                $sheet->getStyle('C1:L1000')->getAlignment()->applyFromArray([
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ]);
            }
        ];
    }
}
