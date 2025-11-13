<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KelasExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $courseId;
    protected $semesterId;
    protected $courseName;

    public function __construct($courseId, $semesterId)
    {
        $this->courseId = $courseId;
        $this->semesterId = $semesterId;
        $this->courseName = Course::where('id', $courseId)->value('name');
    }

    public function collection(): Collection
    {
        $kelas = TeachingSchedule::with('lecturer')
            ->where('activity_id', 1)
            ->where('course_id', $this->courseId)
            ->where('semester_id', $this->semesterId)
            ->orderBy('session_number')
            ->get();

        $data = collect();

        foreach ($kelas as $kel) {
            $data->push([
                'Kelas'   => 'K' . sprintf('%02d', $kel->session_number),
                'Tanggal' => $kel->scheduled_date
                    ? Carbon::parse($kel->scheduled_date)->translatedFormat('D, d M Y')
                    : '',
                'Mulai'   => $kel->start_time
                    ? Carbon::parse($kel->start_time)->format('H:i')
                    : '',
                'Selesai' => $kel->end_time
                    ? Carbon::parse($kel->end_time)->format('H:i')
                    : '',
                'Zona'    => $kel->zone ?? '',
                'Grup'    => $kel->group ?? '',
                'Topik'   => $kel->topic ?? '',
                'Dosen'   => $kel->lecturer->user->name ?? '',
                'Ruang'   => $kel->room ?? '',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Kelas',
            'Tanggal',
            'Mulai',
            'Selesai',
            'Zona',
            'Grup',
            'Topik',
            'Dosen',
            'Ruang',
        ];
    }

    public function title(): string
    {
        return 'Daftar Perkuliahan';
    }

    public function styles(Worksheet $sheet)
    {
        // Styling hanya untuk header tabel
        $sheet->getStyle('A5:I5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'C5E3F7'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 22,
            'C' => 10,
            'D' => 10,
            'E' => 10,
            'F' => 10,
            'G' => 20,
            'H' => 20,
            'I' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // Header atas (judul laporan)
                $event->sheet->setCellValue('A1', 'FAKULTAS KEDOKTERAN UNIVERSITAS TARUMANAGARA');
                $event->sheet->setCellValue('A2', 'JADWAL PERKULIAHAN BLOK: ' . $this->courseName);
                $event->sheet->setCellValue('A3', 'DIEKSPOR PADA: ' . date('d M Y') . ' - ' . date('H:i:s'));
                $event->sheet->setCellValue('A4', ''); // spasi

                // Merge cell untuk header judul
                $event->sheet->mergeCells('A1:I1');
                $event->sheet->mergeCells('A2:I2');
                $event->sheet->mergeCells('A3:I3');
                $event->sheet->mergeCells('A4:I4');
            },

            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();

                // Style header atas
                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                // Border seluruh data
                $sheet->getStyle("A5:I{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Center alignment untuk semua kolom waktu & teks
                $sheet->getStyle("A5:I{$highestRow}")->getAlignment()->setVertical('center');
                $sheet->getStyle("A5:A{$highestRow}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("B5:D{$highestRow}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("E5:F{$highestRow}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("G5:I{$highestRow}")->getAlignment()->setHorizontal('left');

                // Tinggi baris otomatis
                for ($i = 1; $i <= $highestRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(-1);
                }
            },
        ];
    }
}
