<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExamQuestionsAnalysisExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithColumnFormatting, WithTitle
{
    protected $questionsAnalysis;
    protected $rowNumber = 0;

    public function __construct($questionsAnalysis)
    {
        $this->questionsAnalysis = collect($questionsAnalysis);
    }

    public function title(): string
    {
        return 'Question Analysis';
    }

    public function collection()
    {
        return $this->questionsAnalysis;
    }

    public function map($analysis): array
    {
        $this->rowNumber++;

        $question = $analysis->question;
        $totalStudents = $analysis->total_students ?? 0;

        $optionsSummary = $analysis->options_summary ?? [];
        $optionData = [];

        foreach (['A', 'B', 'C', 'D', 'E'] as $letter) {

            $value = $optionsSummary[$letter] ?? null;

            $count = 0;

            if (is_array($value)) {
                $count = $value['total'] ?? 0; // ini yang benar
            }

            $percentage = $totalStudents > 0
                ? $count / $totalStudents
                : 0;

            $optionData[] = $count;
            $optionData[] = $percentage;
        }

        $correctOption = $question->options
            ->where('is_correct', 1)
            ->pluck('option')
            ->implode(', ') ?: 'N/A';

        return array_merge([
            $this->rowNumber,
            strip_tags($question->badan_soal ?? ''),
            strip_tags($question->kalimat_tanya ?? ''),
            $correctOption,
            $totalStudents,
            $analysis->correct_count ?? 0,
            ($analysis->correct_percentage ?? 0) / 100,
            $analysis->difficulty_level ?? '',
            $analysis->discrimination_index ?? 0,
        ], $optionData);
    }

    public function headings(): array
    {
        $optionHeadings = [];
        foreach (['A', 'B', 'C', 'D', 'E'] as $letter) {
            $optionHeadings[] = "Option {$letter} Count";
            $optionHeadings[] = "Option {$letter} %";
        }

        return array_merge([
            'No',
            'Question Text',
            'Question',
            'Correct Option',
            'Total Students',
            'Correct Answers',
            'Correct Percentage',
            'Difficulty Level',
            'Discrimination Index',
        ], $optionHeadings);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // No
            'B' => 50, // Question Text - LEBAR TETAP
            'C' => 30, // Question
            'D' => 12, // Correct Option
            'E' => 12, // Total Students
            'F' => 12, // Correct Answers
            'G' => 12, // Correct Percentage
            'H' => 12, // Difficulty Level
            'I' => 15, // Discrimination Index
            'J' => 12, // Option A Count
            'K' => 12, // Option A %
            'L' => 12, // Option B Count
            'M' => 12, // Option B %
            'N' => 12, // Option C Count
            'O' => 12, // Option C %
            'P' => 12, // Option D Count
            'Q' => 12, // Option D %
            'R' => 12, // Option E Count
            'S' => 12, // Option E %
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->questionsAnalysis) + 1;
        $lastColumn = 'S'; // Sesuaikan dengan jumlah kolom

        // Style untuk header
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Style untuk data
        $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);

        // Center alignment untuk kolom numerik
        $numericColumns = ['A', 'D', 'E', 'F', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];
        foreach ($numericColumns as $col) {
            $sheet->getStyle("{$col}2:{$col}{$lastRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Left alignment untuk kolom teks panjang
        $sheet->getStyle('B2:B' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('C2:C' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Center alignment untuk Difficulty Level
        $sheet->getStyle('G2:G' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER, // Total Students
            'F' => NumberFormat::FORMAT_NUMBER, // Correct Answers
            'G' => NumberFormat::FORMAT_PERCENTAGE_00, // Correct Percentage
            'I' => NumberFormat::FORMAT_NUMBER_00, // Discrimination Index
            'J' => NumberFormat::FORMAT_NUMBER, // Option A Count
            'K' => NumberFormat::FORMAT_PERCENTAGE_00, // Option A %
            'L' => NumberFormat::FORMAT_NUMBER, // Option B Count
            'M' => NumberFormat::FORMAT_PERCENTAGE_00, // Option B %
            'N' => NumberFormat::FORMAT_NUMBER, // Option C Count
            'O' => NumberFormat::FORMAT_PERCENTAGE_00, // Option C %
            'P' => NumberFormat::FORMAT_NUMBER, // Option D Count
            'Q' => NumberFormat::FORMAT_PERCENTAGE_00, // Option D %
            'R' => NumberFormat::FORMAT_NUMBER, // Option E Count
            'S' => NumberFormat::FORMAT_PERCENTAGE_00, // Option E %
        ];
    }
}
