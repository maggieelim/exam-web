<?php

namespace App\Exports;

use App\Models\Exam;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExamResultsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $exam;
    protected $categories;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
        $this->categories = $exam->questions->pluck('category')->unique('id')->values();
    }

    public function collection()
    {
        $this->exam->load(['attempts.user.student', 'answers.user.student', 'answers.question.category', 'questions.category']);

        $results = [];
        $no = 1;

        foreach ($this->exam->attempts as $attempt) {
            $userAnswers = $this->exam->answers->where('user_id', $attempt->user_id);
            $totalAnswer = $this->exam->answers->where('answer', !null)->count();
            $totalQuestions = $this->exam->questions->count();
            $correctAnswers = number_format($userAnswers->where('is_correct', true)->count());

            // Hitung nilai total
            $scorePercentage = $totalQuestions > 0 ? $correctAnswers / $totalQuestions : 0;

            $rowData = [
                'No' => $no++,
                'NIM' => $attempt->user->student->nim ?? '-',
                'Nama' => $attempt->user->name ?? '-',
                'Total Soal' => $totalQuestions,
                'Dijawab' => $totalAnswer,
                'Benar' => $correctAnswers,
            ];

            // Data per kategori - format sebagai string dengan 2 desimal
            foreach ($this->categories as $category) {
                $categoryName = $category->name ?? 'Uncategorized';

                $categoryAnswers = $userAnswers->filter(function ($answer) use ($category) {
                    return $answer->question && $answer->question->category_id == $category->id;
                });

                $categoryCorrect = $categoryAnswers->where('is_correct', true)->count();
                $categoryTotal = $this->exam->questions->where('category_id', $category->id)->count();

                $categoryPercentage = $categoryTotal > 0 ? $categoryCorrect / $categoryTotal : 0;

                // Format sebagai string dengan 2 desimal + simbol %
                $rowData[$categoryName] = number_format(($categoryPercentage * 100) / 100, 4);
            }

            // Nilai akhir - format sebagai string dengan 2 desimal
            $rowData['Total Score (%)'] = number_format(($scorePercentage * 100) / 100, 4);

            $results[] = $rowData;
        }

        return collect($results);
    }

    public function headings(): array
    {
        $categoryHeadings = $this->categories
            ->map(function ($category) {
                return $category->name ?? 'Uncategorized';
            })
            ->toArray();

        return array_merge(['No', 'NIM', 'Nama', 'Total Soal', 'Dijawab', 'Benar'], $categoryHeadings, ['Total Score (%)']);
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        // Header Style
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF5CB6ED'], // warna biru muda #5cb6ed
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Border semua sel
        if ($highestRow > 1) {
            $sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin',
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);

            // Center alignment untuk semua data
            $sheet
                ->getStyle('A2:' . $highestColumn . $highestRow)
                ->getAlignment()
                ->setHorizontal('center')
                ->setVertical('center');
        }

        // Freeze header
        $sheet->freezePane('A2');

        return [];
    }

    public function columnFormats(): array
    {
        // Format sebagai text (tidak ada format khusus)
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_PERCENTAGE_00,
            'H' => NumberFormat::FORMAT_PERCENTAGE_00,
            'I' => NumberFormat::FORMAT_PERCENTAGE_00,
        ];
    }
}
