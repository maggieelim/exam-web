<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamQuestionsExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $exam;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    /**
     * Kumpulkan semua pertanyaan dari exam beserta opsinya
     */
    public function collection()
    {
        // Ambil semua pertanyaan dari exam
        return $this->exam->questions()->with(['options', 'category'])->get();
    }

    /**
     * Header kolom Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'Kategori',
            'Badan Soal',
            'Kalimat Tanya',
            'A',
            'B',
            'C',
            'D',
            'E',
            'Jawaban Benar',
        ];
    }

    /**
     * Mapping tiap baris
     */
    public function map($question): array
    {
        static $no = 1;
        $options = $question->options->pluck('text', 'option')->toArray();

        $labels = ['A', 'B', 'C', 'D', 'E'];
        $optionValues = [];
        foreach ($labels as $label) {
            $optionValues[] = $options[$label] ?? '';
        }

        $correctOption = $question->options->firstWhere('is_correct', true)->option ?? '';

        return [
            $no++, // nomor urut
            $question->category->name ?? '-',
            $question->badan_soal ?? '',
            $question->kalimat_tanya ?? '',
            ...$optionValues,
            $correctOption,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 12,
            'C' => 50,
            'D' => 30,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 12,
            'J' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Gaya untuk header baris pertama
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // putih
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF5CB6ED'], // warna biru muda #5cb6ed
            ],
        ]);

        // Tambahkan border tipis untuk semua sel
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [];
    }
}
