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
    protected $exam;
    protected $questionsAnalysis;
    protected $maxOptions = 0;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
        $this->questionsAnalysis = $this->analyzeQuestions($exam);
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
        $no = $this->questionsAnalysis->search($analysis) + 1;

        // Prepare option analysis data
        $optionData = [];
        $correctOption = '';

        foreach (['A', 'B', 'C', 'D', 'E'] as $index => $optionLetter) {
            $option = $analysis['options']->get($index);
            if ($option) {
                $optionData[] = number_format($option['count'] ?? 0);
                $optionData[] = number_format(($option['percentage'] / 100), 4 ?? 0);

                if ($option['is_correct'] ?? false) {
                    $correctOption = $optionLetter;
                }
            } else {
                $optionData[] = number_format(0);
                $optionData[] = number_format(0);
            }
        }

        return array_merge([
            'No' => $no,
            'Question Text' => strip_tags($analysis['question_text'] ?? ''),
            'Question' => strip_tags($analysis['question'] ?? ''),
            'Correct Option' => $correctOption ?: 'N/A',
            'Total Students' => $analysis['total_students'],
            'Correct Answers' => $analysis['correct_count'],
            'Correct Percentage' => number_format(($analysis['correct_percentage'] / 100), 4),
            'Difficulty Level' => $analysis['difficulty_level'],
            'Discrimination Index' => number_format($analysis['discrimination_index'], 3),
        ], $optionData,);
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
            'M' => NumberFormat::FORMAT_NUMBER, // Option B Count
            'M' => NumberFormat::FORMAT_PERCENTAGE_00, // Option B %
            'N' => NumberFormat::FORMAT_NUMBER, // Option C Count
            'O' => NumberFormat::FORMAT_PERCENTAGE_00, // Option C %
            'P' => NumberFormat::FORMAT_NUMBER, // Option D Count
            'Q' => NumberFormat::FORMAT_PERCENTAGE_00, // Option D %
            'R' => NumberFormat::FORMAT_NUMBER, // Option E Count
            'S' => NumberFormat::FORMAT_PERCENTAGE_00, // Option E %
        ];
    }

    public function analyzeQuestions($exam)
    {
        $totalStudents = $exam->attempts->count();

        return $exam->questions->map(function ($question) use ($exam, $totalStudents) {
            $answers = $exam->answers->where('exam_question_id', $question->id);
            $correct = $answers->where('is_correct', true)->count();
            $correctPercentage = $totalStudents ? round(($correct / $totalStudents) * 100, 2) : 0;

            $options = $question->options->map(function ($opt) use ($answers, $totalStudents) {
                $count = $answers->where('answer', $opt->id)->count();
                return [
                    'option_id' => $opt->id,
                    'option_text' => $opt->text,
                    'is_correct' => $opt->is_correct,
                    'count' => $count,
                    'percentage' => $totalStudents ? round(($count / $totalStudents) * 100, 2) : 0
                ];
            })->values();

            return [
                'question_id' => $question->id,
                'question_text' => $question->badan_soal,
                'question' => $question->kalimat_tanya,
                'image' => $question->image,
                'correct_percentage' => $correctPercentage,
                'correct_count' => $correct,
                'total_students' => $totalStudents,
                'options' => $options,
                'discrimination_index' => $this->calculateDiscriminationIndex($exam, $question),
                'difficulty_level' => $this->getDifficultyLevel($correct, $totalStudents)
            ];
        });
    }

    private function calculateDiscriminationIndex($exam, $question)
    {
        $totalStudents = $exam->attempts->count();
        if ($totalStudents < 10) return 0;

        $studentScores = [];
        foreach ($exam->attempts as $attempt) {
            $userAnswers = $exam->answers->where('user_id', $attempt->user_id);
            $score = $userAnswers->where('is_correct', true)->count();
            $studentScores[$attempt->user_id] = $score;
        }

        arsort($studentScores);
        $userIds = array_keys($studentScores);

        $groupSize = max(1, round($totalStudents * 0.27));
        $topUserIds = array_slice($userIds, 0, $groupSize);
        $bottomUserIds = array_slice($userIds, -$groupSize);

        $topCorrect = $exam->answers
            ->whereIn('user_id', $topUserIds)
            ->where('exam_question_id', $question->id)
            ->where('is_correct', true)
            ->count();

        $bottomCorrect = $exam->answers
            ->whereIn('user_id', $bottomUserIds)
            ->where('exam_question_id', $question->id)
            ->where('is_correct', true)
            ->count();

        $discriminationIndex = ($topCorrect / $groupSize) - ($bottomCorrect / $groupSize);
        return round($discriminationIndex, 3);
    }

    private function getDifficultyLevel($correctAnswers, $totalStudents)
    {
        if ($totalStudents === 0) return 'N/A';
        $ratio = $correctAnswers / $totalStudents;

        if ($ratio >= 0.75) return 'Easy';
        if ($ratio >= 0.2) return 'Fair';
        return 'Hard';
    }
}
