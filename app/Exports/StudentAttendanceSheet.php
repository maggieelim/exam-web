<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentAttendanceSheet implements FromCollection, WithHeadings, WithMapping, WithEvents, WithStyles, WithTitle
{
    protected $records;
    protected $activityName;
    protected $scheduleTime;

    public function __construct($records, $activityName, $scheduleTime)
    {
        $this->records = $records;
        $this->activityName = $activityName;
        $this->scheduleTime = $scheduleTime;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Row 1 & 2
                $sheet->setCellValue('A1', "Kegiatan: " . $this->activityName);
                $sheet->setCellValue('A2', "Waktu: " . $this->scheduleTime);
                $sheet->setCellValue('A3', " ");

                // Style
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
            },
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $startRow = 4; // Header row
                $endRow = $startRow + count($this->records);
                foreach (range('A', 'E') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $dataRange = 'A4:E' . $endRow;

                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);

                $sheet->getStyle('B5:E' . $endRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ]
                ]);

                $sheet->getStyle('A4:E4')->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ]
                ]);

                $sheet->getStyle('A4:E4')->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'C5E3F7']
                    ],
                    'font' => [
                        'bold' => true
                    ]
                ]);
            }
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'NIM',
            'Clocked In',
            'Distance',
            'Status',
        ];
    }

    public function collection()
    {
        return $this->records;
    }

    public function title(): string
    {
        return 'Mahasiswa';
    }

    public function map($record): array
    {
        return [
            $record->courseStudent->student->user->name,
            $record->courseStudent->student->nim,
            $record->scanned_at ? Carbon::parse($record->scanned_at)->format('H:i') : '-',
            $record->distance ? $record->distance . 'm' : '-',
            ucfirst($record->status),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ]
            ],
            // Data rows - left align for name, center for other columns
            'A5:A' . (4 + count($this->records)) => [
                'alignment' => [
                    'horizontal' => 'left',
                    'vertical' => 'center',
                ]
            ],
            'B5:E' . (4 + count($this->records)) => [
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ]
            ]
        ];
    }
}
