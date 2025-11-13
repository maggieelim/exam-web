<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\TeachingSchedule;
use App\Models\CourseLecturer;
use App\Models\PracticumDetails;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PracticumAssignmentExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $courseId;
    protected $semesterId;
    protected $practicums;

    public function __construct($courseId, $semesterId)
    {
        $this->courseId = $courseId;
        $this->semesterId = $semesterId;
        $this->practicums = $this->getPracticums();
    }

    public function collection()
    {
        $lecturers = CourseLecturer::with('lecturer.user')
            ->where('course_id', $this->courseId)
            ->where('semester_id', $this->semesterId)
            ->whereHas('activities', function ($query) {
                $query->where('activity_id', 3);
            })
            ->get();

        $data = collect();
        
        foreach ($lecturers as $index => $courseLecturer) {
            $lecturer = $courseLecturer->lecturer;
            
            $row = [
                'no' => $index + 1,
                'nama_dosen' => $lecturer->user->name,
                'bagian' => $lecturer->bagian,
            ];

            // Tambahkan kolom untuk setiap praktikum dengan key yang konsisten
            foreach ($this->practicums as $practicumIndex => $practicum) {
                $key = 'practicum_' . $practicumIndex;
                
                // Cek apakah dosen ditugaskan di praktikum ini
                $isAssigned = PracticumDetails::where('teaching_schedule_id', $practicum->id)
                    ->where('lecturer_id', $lecturer->id)
                    ->exists();
                
                $row[$key] = $isAssigned ? 'checked' : '-';
            }

            $data->push($row);
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = ['No', 'Nama Dosen', 'Bagian'];

        foreach ($this->practicums as $practicum) {
            $heading = $practicum->topic . "\n" . 
                       $practicum->group . "\n" .
                       Carbon::parse($practicum->scheduled_date)->translatedFormat('D d/M') . "\n" .
                       Carbon::parse($practicum->start_time)->format('H:i') . "\n" . Carbon::parse($practicum->end_time)->format('H:i');
            
            $headings[] = $heading;
        }

        return $headings;
    }

    public function title(): string
    {
        $course = Course::find($this->courseId);
        return $course ? 'Penugasan Praktikum - ' . $course->name : 'Penugasan Praktikum';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Style untuk header
        if ($lastRow >= 1) {
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
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];

            $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);

        }

        // Style untuk data
        if ($lastRow > 1) {
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

            $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray($dataStyle);

            // Center alignment untuk kolom No dan checkbox
            $sheet->getStyle('A2:A' . $lastRow)
                  ->getAlignment()
                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Center alignment untuk kolom praktikum (mulai dari kolom D)
            if ($lastColumn >= 'D') {
                $firstPracticumCol = 'D';
                $lastPracticumCol = $lastColumn;
                $sheet->getStyle($firstPracticumCol . '2:' . $lastPracticumCol . $lastRow)
                      ->getAlignment()
                      ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }

            // Left alignment untuk kolom nama dan bagian
            $sheet->getStyle('B2:C' . $lastRow)
                  ->getAlignment()
                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            // Wrap text untuk semua sel
            $sheet->getStyle('A1:' . $lastColumn . $lastRow)
                  ->getAlignment()
                  ->setWrapText(true);
        }

        // Freeze panes dan row height
        if ($lastRow > 1) {
            $sheet->freezePane('D2');
        }
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,   // No
            'B' => 30,  // Nama Dosen
            'C' => 20,  // Bagian
        ];

        // Set width untuk kolom praktikum
        $practicumCount = count($this->practicums);
        for ($i = 0; $i < $practicumCount; $i++) {
            $column = chr(68 + $i); // D, E, F, ...
            $widths[$column] = 11;
        }
        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Pastikan data sesuai dengan header
                $sheet = $event->sheet->getDelegate();
                
                // Debug info - bisa dihapus setelah testing
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                \Log::info("Excel generated: {$highestRow} rows, {$highestColumn} columns");
                \Log::info("Practicums count: " . count($this->practicums));
            },
        ];
    }

    /**
     * Get practicums data
     */
    private function getPracticums()
    {
        return TeachingSchedule::whereIn('activity_id', [3, 7])
            ->where('course_id', $this->courseId)
            ->where('semester_id', $this->semesterId)
            ->whereNotNull('scheduled_date')
            ->with('practicumDetails')
            ->orderBy('session_number')
            ->get();
    }
}