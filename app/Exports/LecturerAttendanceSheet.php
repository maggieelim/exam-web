<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LecturerAttendanceSheet implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithEvents,
    ShouldAutoSize,
    WithStyles,
    WithTitle
{
    protected $records;
    protected $activityName;
    protected $scheduleTime;

    public function __construct($records, $activityName, $scheduleTime)
    {
        $this->records       = $records;
        $this->activityName  = $activityName;
        $this->scheduleTime  = $scheduleTime;
    }

    /**
     * Title rows before the table.
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Title & Schedule Info - ditulis di row 1-3
                $sheet->setCellValue('A1', "Kegiatan: " . $this->activityName);
                $sheet->setCellValue('A2', "Waktu: " . $this->scheduleTime);
                $sheet->setCellValue('A3', " ");
                // Bold titles
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
                $dataRange = 'A4:C' . $endRow;

                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);

                $sheet->getStyle('B5:C' . $endRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ]
                ]);

                $sheet->getStyle('A4:C4')->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ]
                ]);

                $sheet->getStyle('A4:C4')->applyFromArray([
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

    public function title(): string
    {
        return 'Dosen';
    }

    public function headings(): array
    {
        return [
            ['Lecturer Name', 'Clocked In', 'Status'], // Headings akan dimulai di row 4
        ];
    }

    public function collection()
    {
        return $this->records;
    }

    public function map($record): array
    {
        $status = $record->status === 'checked_in'
            ? 'Present'
            : 'Absent';
        return [
            $record->courseLecturer->lecturer->user->name,
            $record->checked_in_at
                ? Carbon::parse($record->checked_in_at)->format('H:i')
                : '-',
            $status,
        ];
    }

    /**
     * Style whole sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Header row (row 4)
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
            'B5:C' . (4 + count($this->records)) => [
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ]
            ]
        ];
    }
}
