<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllNilaiPemicuExport implements FromView, WithStyles, WithEvents
{
    public function __construct(
        public $pemicuGroups,
        public $preGroup,
        public $course,
        public $groupedStudents,
        public $scores,
        public $groupLecturer,
        public $teachingSchedules
    ) {}

    public function view(): View
    {
        // Validasi data sebelum mengirim ke view
        if (!$this->course || !$this->groupedStudents) {
            throw new \Exception("Data course atau students tidak valid");
        }

        return view('courses.pemicu.all_nilai_pemicu', [
            'pemicuGroups' => $this->pemicuGroups ?? [],
            'preGroup' => $this->preGroup ?? 0,
            'course' => $this->course,
            'groupedStudents' => $this->groupedStudents ?? collect(),
            'scores' => $this->scores ?? collect(),
            'groupLecturer' => $this->groupLecturer ?? collect(),
            'teachingSchedules' => $this->teachingSchedules ?? collect(),
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ],
            2 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set column widths
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $last = $sheet->getHighestColumn();
                $sheet->getColumnDimension($last)->setAutoSize(true);

                // Header style
                $headerStyle = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9EAF7']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];

                $lastCol = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle('A1:' . $lastCol . '2')->applyFromArray($headerStyle);

                // Apply borders to all data
                $sheet->getStyle('A1:' . $lastCol . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                ]);

                // Center alignment for score columns
                $scoreColStart = 'C';
                $scoreColEnd = chr(ord($scoreColStart) + (count($this->pemicuGroups ?? []) * 2) - 1);
                $sheet->getStyle($scoreColStart . '1:' . $scoreColEnd . $highestRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }
        ];
    }
}
