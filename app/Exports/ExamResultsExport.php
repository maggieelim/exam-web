<?php

namespace App\Exports;

use App\Models\Exam;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExamResultsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    protected $results;
    protected $categories;

    public function __construct($results)
    {
        // Kalau paginator
        if ($results instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $this->results = $results->getCollection();
        } else {
            $this->results = collect($results);
        }

        $first = $this->results->first();

        $this->categories = $first
            ? collect($first['categories_result'])
            : collect();
    }

    public function collection()
    {
        $no = 1;

        return collect($this->results)->map(function ($row) use (&$no) {

            $categories = $row['categories_result'] ?? [];

            $rowData = [
                'No' => $no++,
                'NIM' => $row['student_data']->nim ?? '-',
                'Nama' => $row['student']->name ?? '-',
                'Total Soal' => $row['total_questions'] ?? 0,
                'Dijawab' => $row['total_answered'] ?? 0,
                'Benar' => $row['correct_answers'] ?? 0,
            ];

            foreach ($categories as $category) {
                $rowData[$category['category_name']] =
                    isset($category['percentage'])
                    ? (float) $category['percentage'] / 100
                    : 0;
            }

            $rowData['Total Score (%)'] =
                isset($row['score_percentage'])
                ? (float) $row['score_percentage'] / 100
                : 0;

            return $rowData;
        });
    }

    public function headings(): array
    {
        $categoryHeadings = $this->categories
            ->pluck('category_name')
            ->toArray();

        return array_merge(
            ['No', 'NIM', 'Nama', 'Total Soal', 'Dijawab', 'Benar'],
            $categoryHeadings,
            ['Total Score (%)']
        );
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $startColumn = 7;
                $totalPercentageColumns = $this->categories->count() + 1;

                for ($i = 0; $i < $totalPercentageColumns; $i++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($startColumn + $i);

                    $event->sheet->getStyle($columnLetter . '1:' . $columnLetter . $event->sheet->getHighestRow())
                        ->getAlignment()
                        ->setWrapText(true);
                }
            },
        ];
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
        $formats = [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
        ];

        // Kolom persentase mulai dari G
        $startColumn = 7;
        $totalPercentageColumns = $this->categories->count() + 1; // + total score

        for ($i = 0; $i < $totalPercentageColumns; $i++) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumn + $i);
            $formats[$columnLetter] = NumberFormat::FORMAT_PERCENTAGE_00;
        }
        return $formats;
    }
}
