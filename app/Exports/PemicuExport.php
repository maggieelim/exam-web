<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\PemicuDetails;
use App\Models\TeachingSchedule;
use App\Services\LecturerSortService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PemicuExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $courseId;
    protected $semesterId;
    protected $pemicu;

    public function __construct($courseId, $semesterId)
    {
        $this->courseId = $courseId;
        $this->semesterId = $semesterId;
        $this->pemicu = $this->getPemicu();
    }

    public function collection()
    {
        $sorter = app(LecturerSortService::class);
        $lecturers = CourseLecturer::with('lecturer.user')
            ->where('course_id', $this->courseId)
            ->where('semester_id', $this->semesterId)
            ->whereHas('activities', function ($query) {
                $query->where('activity_id', 5);
            })
            ->get();

        $lecturers = $sorter->sort($lecturers, $this->courseId, $this->semesterId);

        $data = collect();

        foreach ($lecturers as $index => $courseLecturer) {
            $lecturer = $courseLecturer->lecturer;

            $row = [
                'no' => $index + 1,
                'nama_dosen' => $lecturer->user->name,
                'bagian' => $lecturer->bagian,
            ];

            foreach ($this->pemicu as $pemicuIndex => $pemicu) {
                $key = 'pemicu_' . $pemicuIndex;
                $pemicuValue = PemicuDetails::where([
                    ['teaching_schedule_id', $pemicu->id],
                    ['lecturer_id', $lecturer->id]
                ])->value('kelompok_num') ?? '-';

                $row[$key] = $pemicuValue;
            }

            $data->push($row);
        }

        return $data;
    }

    public function headings(): array
    {
        $firstRow = ['No', 'Nama Dosen', 'Bagian'];
        $secondRow = ['', '', ''];

        foreach ($this->pemicu->take(ceil($this->pemicu->count() / 2)) as $pemicu) {
            $firstRow[] = 'Pemicu ' . $pemicu->session_number;
            $firstRow[] = ''; // karena tiap "Pemicu" punya 2 kolom di bawahnya
        }
        foreach ($this->pemicu as $pemicu) {
            $date = Carbon::parse($pemicu->scheduled_date)->translatedFormat('D d/M');
            $time = Carbon::parse($pemicu->start_time)->format('H:i') . '~' . Carbon::parse($pemicu->end_time)->format('H:i');
            $secondRow[] = $date . "\n" . $time;
        }

        return [
            $firstRow,
            $secondRow
        ];
    }

    public function title(): string
    {
        $course = Course::find($this->courseId);
        return $course ? 'Penugasan Pemicu - ' . $course->name : 'Penugasan Pemicu';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Style untuk header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A1:' . $lastColumn . '2')->applyFromArray($headerStyle);

        // Style untuk data
        if ($lastRow > 2) {
            $dataStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ];

            $sheet->getStyle('A3:' . $lastColumn . $lastRow)->applyFromArray($dataStyle);

            // Alignment untuk kolom
            $sheet->getStyle('A3:A' . $lastRow)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('B3:C' . $lastRow)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            // Center alignment untuk kolom Pemicu
            $firstPracticumCol = 'D';
            $sheet->getStyle($firstPracticumCol . '3:' . $lastColumn . $lastRow)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Wrap text untuk semua sel
            $sheet->getStyle('A1:' . $lastColumn . $lastRow)
                ->getAlignment()
                ->setWrapText(true);
        }

        // Freeze panes
        if ($lastRow > 2) {
            $sheet->freezePane('D3');
        }
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,  // No
            'B' => 30, // Nama Dosen
            'C' => 20, // Bagian
        ];

        // Set width untuk kolom Pemicu
        $pemicuCount = count($this->pemicu);
        for ($i = 0; $i < $pemicuCount; $i++) {
            $column = chr(68 + $i); // D, E, F, ...
            $widths[$column] = 15;
        }
        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge cells untuk header
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $colIndex = 4; // kolom D
                foreach ($this->pemicu->take(ceil($this->pemicu->count() / 2)) as $pemicu) {
                    $colStart = $colIndex;
                    $colEnd = $colIndex + 1; // tiap pemicu punya 2 kolom
                    $sheet->mergeCellsByColumnAndRow($colStart, 1, $colEnd, 1);
                    $colIndex += 2;
                }
                // Set row height untuk header

            },
        ];
    }

    private function getPemicu()
    {
        return TeachingSchedule::where('activity_id', 5)
            ->where('course_id', $this->courseId)
            ->where('semester_id', $this->semesterId)
            ->whereNotNull('scheduled_date')
            ->with('pemicuDetails')
            ->orderBy('session_number')
            ->get();
    }
}
