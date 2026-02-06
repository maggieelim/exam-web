<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\PlenoDetails;
use App\Models\TeachingSchedule;
use App\Services\LecturerSortService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PlenoExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $courseId;
    protected $semesterId;
    protected $plenos;

    public function __construct($courseId, $semesterId)
    {
        $this->courseId = $courseId;
        $this->semesterId = $semesterId;
        $this->plenos = $this->getPlenos();
    }

    public function collection()
    {
        $sorter = app(LecturerSortService::class);
        $lecturers = CourseLecturer::with('lecturer.user')
            ->where('course_id', $this->courseId)
            ->where('semester_id', $this->semesterId)
            ->whereHas('activities', fn($q) => $q->where('activity_id', 4))
            ->get();

        $lecturers = $sorter->sort($lecturers, $this->courseId, $this->semesterId);

        $data = new Collection();

        foreach ($lecturers as $index => $courseLecturer) {
            $lecturer = $courseLecturer->lecturer;

            $row = [
                'No' => $index + 1,
                'Nama Dosen' => $lecturer->user->name . ', ' . $lecturer->gelar,
                'Bagian' => $lecturer->bagian ?? '-',
            ];

            foreach ($this->plenos as $plenoIndex => $pleno) {
                $isAssigned = PlenoDetails::where('teaching_schedule_id', $pleno->id)
                    ->where('lecturer_id', $lecturer->id)
                    ->exists();

                $row['Pleno_' . ($plenoIndex + 1)] = $isAssigned ? 'Checked' : '-';
            }

            $data->push($row);
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = ['No', 'Nama Dosen', 'Bagian'];

        foreach ($this->plenos as $pleno) {
            $heading = "P" . $pleno->session_number . "\n" .
                Carbon::parse($pleno->scheduled_date)->translatedFormat('D, d M') . "\n" .
                Carbon::parse($pleno->start_time)->format('H:i') . " - " . Carbon::parse($pleno->end_time)->format('H:i');

            $headings[] = $heading;
        }

        return $headings;
    }

    public function title(): string
    {
        $course = Course::find($this->courseId);
        return $course ? 'Penugasan Pleno - ' . $course->name : 'Penugasan Pleno';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
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
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray($headerStyle);

        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->applyFromArray($dataStyle);
        $sheet->getStyle("A2:A{$lastRow}")
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B2:C{$lastRow}")
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $firstPlenoCol = Coordinate::stringFromColumnIndex(4);
        $sheet->getStyle("{$firstPlenoCol}2:{$lastColumn}{$lastRow}")
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
            ->getAlignment()->setWrapText(true);

        $sheet->freezePane('D2');
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,
            'B' => 30,
            'C' => 20,
        ];

        $plenoCount = count($this->plenos);
        for ($i = 1; $i <= $plenoCount; $i++) {
            $column = Coordinate::stringFromColumnIndex($i + 3);
            $widths[$column] = 12;
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getDefaultRowDimension()->setRowHeight(-1); // auto height
            },
        ];
    }

    private function getPlenos()
    {
        // digabung (merge) berdasarkan kelas
        return TeachingSchedule::where('activity_id', 4)
            ->where('course_id', $this->courseId)
            ->where('semester_id', $this->semesterId)
            ->whereNotNull('scheduled_date')
            ->with(['plenoDetails'])
            ->orderBy('session_number')
            ->orderBy('scheduled_date')
            ->get();
    }
}
