<?php

namespace App\Exports;

use App\Models\CourseStudent;
use App\Models\TeachingSchedule;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SkillsLabPresenceForm implements WithMultipleSheets
{
    protected $courseId;
    protected $semesterId;
    protected $sessions; // Mengganti kkdSessions menjadi sessions untuk semua tipe
    protected $studentGroups;
    protected $courseName;
    protected $academicYear;
    protected $maxSessionsPerSheet = 6;
    protected $activityTypes = [2, 3, 5, 4]; // KKD (2), Praktikum (3), Pemicu (5), TBL (4)

    public function __construct($course_id, $semesterId, $courseName, $academicYear)
    {
        $this->courseId = $course_id;
        $this->semesterId = $semesterId;
        $this->courseName = $courseName;
        $this->academicYear = $academicYear;
        $this->sessions = $this->getAllSessions();
        $this->studentGroups = $this->getStudentGroups();
    }

    public function sheets(): array
    {
        $sheets = [];
        $sheetNumber = 1;

        // Kelompokkan sessions berdasarkan activity_id terlebih dahulu
        $groupedSessions = $this->sessions->groupBy('activity_id');

        foreach ($groupedSessions as $activityId => $sessions) {
            $activityType = $this->getActivityTypeLabel($activityId);
            $totalSessions = count($sessions);

            // Bagi sessions per activity menjadi beberapa sheet jika lebih dari 4
            $sessionChunks = $sessions->chunk($this->maxSessionsPerSheet);
            $chunkNumber = 1;

            foreach ($sessionChunks as $chunkIndex => $sessionChunk) {
                $sheets[] = new PresenceSheet(
                    $this->courseId,
                    $this->semesterId,
                    $this->courseName,
                    $this->academicYear,
                    $sessionChunk,
                    $this->studentGroups,
                    $sheetNumber,
                    $totalSessions,
                    $activityType,
                    $chunkNumber // Tambahkan parameter chunkNumber
                );
                $sheetNumber++;
                $chunkNumber++;
            }
        }
        return $sheets;
    }

    private function getAllSessions()
    {
        return TeachingSchedule::whereIn('activity_id', $this->activityTypes)
            ->where('course_id', $this->courseId)
            ->where('semester_id', $this->semesterId)
            ->whereNotNull('scheduled_date')
            ->orderBy('activity_id')
            ->orderBy('session_number')
            ->get();
    }

    private function getActivityTypeLabel($activityId)
    {
        switch ($activityId) {
            case 2:
                return 'KKD';
            case 3:
                return 'Praktikum';
            case 5:
                return 'Pemicu';
            case 4:
                return 'TBL';
            default:
                return 'KKD';
        }
    }

    private function getStudentGroups()
    {
        return CourseStudent::with('student.user')
            ->where('course_id', $this->courseId)
            ->where('semester_id', $this->semesterId)
            ->orderBy('kelompok')
            ->get()
            ->groupBy('kelompok');
    }
}

class PresenceSheet implements FromArray, WithEvents, WithTitle
{
    protected $courseId;
    protected $semesterId;
    protected $sessions;
    protected $studentGroups;
    protected $courseName;
    protected $academicYear;
    protected $sheetNumber;
    protected $totalSessions;
    protected $activityType;
    protected $maxSessionsPerSheet = 6;
    protected $chunkNumber;

    public function __construct($courseId, $semesterId, $courseName, $academicYear, $sessions, $studentGroups, $sheetNumber, $totalSessions, $activityType, $chunkNumber)
    {
        $this->courseId = $courseId;
        $this->semesterId = $semesterId;
        $this->sessions = $sessions;
        $this->studentGroups = $studentGroups;
        $this->courseName = $courseName;
        $this->academicYear = $academicYear;
        $this->sheetNumber = $sheetNumber;
        $this->totalSessions = $totalSessions;
        $this->activityType = $activityType;
        $this->chunkNumber = $chunkNumber;
    }

    public function array(): array
    {
        $rows = [];
        if ($this->isPemicu()) {
            $pemicuCount = $this->groupPemicuSessions()->count();
            $totalColumns = 3 + ($pemicuCount * 3); // D1 & D2
        } else {
            $sessionCount = count($this->sessions);
            $totalColumns = 3 + $sessionCount;
        }

        $groupIndex = 0;
        foreach ($this->studentGroups as $kelompok => $items) {
            $groupIndex++;

            // Tambahkan baris header untuk setiap kelompok
            $this->addGroupHeaderRows($rows, $kelompok, $items->count(), $totalColumns);

            // Tambahkan data mahasiswa
            $this->addStudentRows($rows, $items, $totalColumns);

            // Tambahkan baris tutor, paraf, dan tanggal
            $this->addTutorRows($rows, $totalColumns);

            // Tambahkan baris kosong antar kelompok (kecuali untuk kelompok terakhir)
            if ($groupIndex < count($this->studentGroups)) {
                for ($i = 0; $i < 4; $i++) {
                    $rows[] = array_fill(0, $totalColumns, '');
                }
            }
        }

        return $rows;
    }

    private function addGroupHeaderRows(&$rows, $kelompok, $studentCount, $totalColumns)
    {
        $titleRow = array_fill(0, $totalColumns, '');
        $titleRow[0] = 'DAFTAR HADIR ' . strtoupper($this->activityType) . ' MAHASISWA';
        $rows[] = $titleRow;

        $blockRow = array_fill(0, $totalColumns, '');
        $blockRow[0] = 'BLOK: ' . strtoupper($this->courseName);
        if ($this->isPemicu()) {
            $blockRow[$totalColumns - 3] = 'SPMI-20-/FR-FK-20-11-R0';
        } else {
            $blockRow[$totalColumns - 2] = 'SPMI-20-/FR-FK-20-11-R0';
        }
        $blockRow[$totalColumns - 2] = 'SPMI-20-/FR-FK-20-11-R0';
        $rows[] = $blockRow;

        $semesterRow = array_fill(0, $totalColumns, '');
        $semesterRow[0] = 'SEMESTER ' . strtoupper($this->academicYear);
        $rows[] = $semesterRow;

        // spacer
        $rows[] = array_fill(0, $totalColumns, '');
        $rows[] = array_fill(0, $totalColumns, '');

        /* ===============================
     * HEADER TABEL
     * =============================== */
        $headerRow1 = array_fill(0, $totalColumns, '');
        $headerRow2 = array_fill(0, $totalColumns, '');

        $headerRow1[0] = 'NO';
        $headerRow1[1] = 'NIM';
        $headerRow1[2] = 'NAMA';

        if ($this->isPemicu()) {

            // ===== PEMICU MODE =====
            $colIndex = 3;

            foreach ($this->groupPemicuSessions() as $pemicuKe => $sessions) {

                // Baris 1 → PEMICU i (merge 2 kolom)
                $headerRow1[$colIndex] = 'PEMICU ' . $pemicuKe;

                // Baris 2 → D1 & D2
                $headerRow2[$colIndex]     = 'D1';
                $headerRow2[$colIndex + 1] = 'D2';
                $headerRow1[$colIndex + 2] = 'PLENO ' . $pemicuKe;

                $colIndex += 3;
            }
        } else {

            // ===== NON PEMICU MODE =====
            $sessionIndex = 0;
            foreach ($this->sessions as $session) {
                $headerRow1[3 + $sessionIndex] =
                    strtoupper($this->activityType) . ' ' . $session->session_number;
                $sessionIndex++;
            }
        }

        // push header rows
        $rows[] = $headerRow1;

        // baris kelompok + D1 D2 / kosong
        $headerRow2[0] = 'Kelompok: ' . $kelompok . '  (Jumlah=' . $studentCount . ' Siswa)';
        $rows[] = $headerRow2;
    }

    private function addStudentRows(&$rows, $items, $totalColumns)
    {
        foreach ($items as $i => $student) {
            $studentRow = array_fill(0, $totalColumns, '');
            $studentRow[0] = $i + 1;
            $studentRow[1] = $student->student->nim ?? '-';
            $studentRow[2] = $student->student->user->name ?? '-';
            $rows[] = $studentRow;
        }
        $emptyRow = array_fill(0, $totalColumns, '');
        $emptyRow[0] = count($items) + 1; // nomor urut berikutnya
        $rows[] = $emptyRow;
    }

    private function addTutorRows(&$rows, $totalColumns)
    {
        $tutorRows = [
            ['label' => 'NAMA TUTOR:', 'placeholder' => '…………..'],
            ['label' => 'PARAF TUTOR:', 'placeholder' => '…………..'],
            ['label' => 'TANGGAL:', 'placeholder' => '…………..']
        ];

        foreach ($tutorRows as $rowData) {
            $row = array_fill(0, $totalColumns, '');
            $row[0] = $rowData['label'];

            for ($col = 3; $col < $totalColumns; $col++) {
                $row[$col] = $rowData['placeholder'];
            }

            $rows[] = $row;
        }
    }

    public function title(): string
    {
        $startSession = (($this->chunkNumber - 1) * $this->maxSessionsPerSheet) + 1;
        $endSession = $startSession + count($this->sessions) - 1;

        return $this->activityType . ' ' . $startSession . '-' . $endSession;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->setupPage($sheet);

                $sessionCount = count($this->sessions);
                if ($this->isPemicu()) {
                    $pemicuCount = $this->groupPemicuSessions()->count();
                    $totalColumns = 3 + ($pemicuCount * 3); // D1 & D2
                } else {
                    $sessionCount = count($this->sessions);
                    $totalColumns = 3 + $sessionCount;
                }
                $lastColumn = Coordinate::stringFromColumnIndex($totalColumns);

                $currentRow = 1;
                $groupNumber = 0;
                $totalGroups = count($this->studentGroups);

                foreach ($this->studentGroups as $kelompok => $items) {
                    $groupNumber++;
                    $studentCount = $items->count();

                    // HITUNG JUMLAH BARIS YANG SUDAH DIBUAT UNTUK KELOMPOK INI
                    $groupRows = $this->calculateGroupRows($studentCount);
                    $groupEndRow = $currentRow + $groupRows - 1;

                    // FORMAT KELOMPOK INI
                    $this->formatGroup($sheet, $currentRow, $kelompok, $studentCount, $sessionCount, $totalColumns, $lastColumn, $groupRows);

                    // TAMBAHKAN PAGE BREAK SETELAH KELOMPOK (kecuali kelompok terakhir)
                    if ($groupNumber < $totalGroups) {
                        // Page break ditempatkan di baris KOSONG SETELAH kelompok
                        $pageBreakRow = $groupEndRow + 4; // Baris pertama dari 4 baris kosong
                        $sheet->setBreak("A{$pageBreakRow}", Worksheet::BREAK_ROW);

                        // UPDATE currentRow untuk kelompok berikutnya (termasuk 4 baris kosong)
                        $currentRow = $groupEndRow + 5; // +4 baris kosong +1 untuk baris berikutnya
                    } else {
                        // Untuk kelompok terakhir, tidak perlu update currentRow
                        $currentRow = $groupEndRow + 1;
                    }
                }

                $this->setColumnWidths($sheet, $sessionCount);
                $this->autoAdjustRowHeights($sheet, $lastColumn);
            }
        ];
    }

    private function calculateGroupRows($studentCount)
    {
        return 5 + 2 + ($studentCount + 1) + 3;
    }

    private function setupPage($sheet)
    {
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

        // PENGATURAN PAGE LAYOUT
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Atur margin untuk optimal
        $sheet->getPageMargins()->setTop(0.4);
        $sheet->getPageMargins()->setRight(0.3);
        $sheet->getPageMargins()->setLeft(0.3);
        $sheet->getPageMargins()->setBottom(0.4);
        $sheet->getPageMargins()->setHeader(0.2);
        $sheet->getPageMargins()->setFooter(0.2);
    }

    private function formatGroup($sheet, $startRow, $kelompok, $studentCount, $sessionCount, $totalColumns, $lastColumn, $groupRows)
    {
        $groupStartRow = $startRow;
        $groupEndRow = $groupStartRow + $groupRows - 1;

        // Set row heights hanya untuk baris yang berisi data (bukan baris kosong)
        for ($row = $groupStartRow; $row <= $groupEndRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(23);
        }

        $sheet->getStyle("A{$groupStartRow}:{$lastColumn}{$groupEndRow}")
            ->getFont()
            ->setSize(12);

        // Format judul, blok, dan semester
        $this->formatHeaderRows($sheet, $groupStartRow, $lastColumn, $totalColumns);

        // Baris header tabel dimulai dari baris ke-6 dari awal kelompok (0-based)
        $headerRow1 = $groupStartRow + 5; // Baris ke-6
        $headerRow2 = $headerRow1 + 1;

        // Data mahasiswa dimulai setelah header tabel
        $dataStartRow = $headerRow2 + 1;
        $dataEndRow = $dataStartRow + $studentCount;

        // Baris tutor dimulai setelah data mahasiswa
        $tutorStartRow = $dataEndRow + 1;
        $dateRow = $tutorStartRow + 2;
        // Format header tabel
        $this->formatTableHeaders($sheet, $headerRow1, $headerRow2, $sessionCount, $lastColumn);

        // Format data mahasiswa
        $this->formatStudentData($sheet, $dataStartRow, $dataEndRow, $lastColumn, $sessionCount);

        // Format baris tutor
        $this->formatTutorRows($sheet, $tutorStartRow, $dateRow, $lastColumn, $sessionCount);

        // Apply borders - dari header tabel sampai baris tanggal
        $this->applyBorders($sheet, $headerRow1, $dateRow, $lastColumn, $headerRow1, $dataEndRow);

        $ranges = [
            "A{$headerRow1}:C{$dateRow}",
            "A{$tutorStartRow}:{$lastColumn}{$dateRow}",
            "A{$headerRow1}:{$lastColumn}{$headerRow2}"
        ];

        $style = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        if ($this->isPemicu()) {
            for ($colIndex = 4; $colIndex <= $totalColumns; $colIndex += 3) {
                $endColIndex = min($colIndex + 2, $totalColumns);

                $startCol = Coordinate::stringFromColumnIndex($colIndex);
                $endCol = Coordinate::stringFromColumnIndex($endColIndex);

                // Apply border outline tebal untuk grup kolom ini
                $range = "{$startCol}{$headerRow1}:{$endCol}{$dateRow}";
                $sheet->getStyle($range)->applyFromArray($style);
            }
        } else {
            for ($colIndex = 4; $colIndex <= $totalColumns; $colIndex += 1) {
                $endColIndex = min($colIndex + 2, $totalColumns);
                $startCol = Coordinate::stringFromColumnIndex($colIndex);
                $endCol = Coordinate::stringFromColumnIndex($endColIndex);

                // Apply border outline tebal untuk grup kolom ini
                $range = "{$startCol}{$headerRow1}:{$endCol}{$dateRow}";
                $sheet->getStyle($range)->applyFromArray($style);
            }
        }
        foreach ($ranges as $range) {
            $sheet->getStyle($range)->applyFromArray($style);
        }

        // Set row height khusus untuk baris data mahasiswa
        for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
        }
    }

    private function formatHeaderRows($sheet, $startRow, $lastColumn, $totalColumns)
    {
        $titleRow = $startRow;
        $blockRow = $titleRow + 1;
        $semesterRow = $blockRow + 1;

        // Title row
        $sheet->mergeCells("A{$titleRow}:{$lastColumn}{$titleRow}");
        $this->applyCellStyle($sheet, "A{$titleRow}", ['bold' => true, 'size' => 14, 'horizontal' => 'left']);

        // Block row
        $this->applyCellStyle($sheet, "A{$blockRow}", ['bold' => true, 'size' => 12, 'horizontal' => 'left']);

        // SPMI cells
        $spmiColStart = $this->isPemicu() ? Coordinate::stringFromColumnIndex($totalColumns - 2) : Coordinate::stringFromColumnIndex($totalColumns - 1);
        $spmiColEnd = Coordinate::stringFromColumnIndex($totalColumns);
        $sheet->mergeCells("{$spmiColStart}{$blockRow}:{$spmiColEnd}{$blockRow}");
        $this->applyCellStyle($sheet, "{$spmiColStart}{$blockRow}", ['bold' => true, 'size' => 11, 'horizontal' => 'center']);
        $this->applyBorder($sheet, "{$spmiColStart}{$blockRow}:{$spmiColEnd}{$blockRow}", Border::BORDER_MEDIUM);

        // Semester row
        $sheet->mergeCells("A{$semesterRow}:{$lastColumn}{$semesterRow}");
        $this->applyCellStyle($sheet, "A{$semesterRow}", ['bold' => true, 'size' => 12, 'horizontal' => 'left']);
    }

    private function formatTableHeaders($sheet, $headerRow1, $headerRow2, $sessionCount, $lastColumn)
    {
        // Header row 1 style
        $sheet->getStyle("A{$headerRow1}:{$lastColumn}{$headerRow1}")
            ->getFont()->setBold(true);

        $sheet->getStyle("A{$headerRow1}:{$lastColumn}{$headerRow1}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Merge and format session columns
        if ($this->isPemicu()) {
            $pemicuCount = $this->groupPemicuSessions()->count();
            $colIndex = 4; // kolom D

            for ($i = 0; $i < $pemicuCount; $i++) {

                $pemicuStart = Coordinate::stringFromColumnIndex($colIndex);
                $pemicuEnd   = Coordinate::stringFromColumnIndex($colIndex + 1);

                $sheet->mergeCells("{$pemicuStart}{$headerRow1}:{$pemicuEnd}{$headerRow1}");
                $this->applyCellStyle($sheet, "{$pemicuStart}{$headerRow1}", [
                    'bold' => true,
                    'horizontal' => 'center'
                ]);

                // D1 & D2
                $this->applyCellStyle($sheet, "{$pemicuStart}{$headerRow2}", [
                    'bold' => true,
                    'horizontal' => 'center'
                ]);
                $this->applyCellStyle($sheet, "{$pemicuEnd}{$headerRow2}", [
                    'bold' => true,
                    'horizontal' => 'center'
                ]);

                $plenoCol = Coordinate::stringFromColumnIndex($colIndex + 2);
                $sheet->mergeCells("{$plenoCol}{$headerRow1}:{$plenoCol}{$headerRow2}");

                $this->applyCellStyle($sheet, "{$plenoCol}{$headerRow1}", [
                    'bold' => true,
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ]);

                // Geser ke pemicu berikutnya
                $colIndex += 3;
            }
        } else {
            for ($i = 0; $i < $sessionCount; $i++) {
                $colLetter = Coordinate::stringFromColumnIndex(4 + $i);
                $sheet->mergeCells("{$colLetter}{$headerRow1}:{$colLetter}{$headerRow2}");
            }
        }

        // Header row 2 (kelompok)
        $sheet->mergeCells("A{$headerRow2}:C{$headerRow2}");
        $this->applyCellStyle($sheet, "A{$headerRow2}", ['bold' => true, 'horizontal' => 'left']);
    }

    private function formatStudentData($sheet, $dataStartRow, $dataEndRow, $lastColumn, $sessionCount)
    {
        // Align student data
        $sheet->getStyle("B{$dataStartRow}:C{$dataEndRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        if ($sessionCount > 0) {
            $sheet->getStyle("D{$dataStartRow}:{$lastColumn}{$dataEndRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
    }

    private function formatTutorRows($sheet, $tutorStartRow, $dateRow, $lastColumn, $sessionCount)
    {
        $rows = [
            $tutorStartRow => 'NAMA TUTOR:',
            $tutorStartRow + 1 => 'PARAF TUTOR:',
            $dateRow => 'TANGGAL:'
        ];

        foreach ($rows as $rowNum => $label) {
            $sheet->mergeCells("A{$rowNum}:C{$rowNum}");
            $this->applyCellStyle($sheet, "A{$rowNum}", ['bold' => true, 'horizontal' => 'left']);
            $sheet->getRowDimension($rowNum)->setRowHeight(30);
        }

        if ($sessionCount > 0) {
            $tutorRange = "D{$tutorStartRow}:{$lastColumn}{$dateRow}";
            $sheet->getStyle($tutorRange)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_BOTTOM);
        }
    }

    private function applyBorders($sheet, $startRow, $endRow, $lastColumn, $headerRow1, $dataEndRow)
    {
        $range = "A{$startRow}:{$lastColumn}{$endRow}";
        $this->applyBorder($sheet, $range, Border::BORDER_THIN);
    }

    private function applyCellStyle($sheet, $cell, $options = [])
    {
        $style = $sheet->getStyle($cell);

        if (isset($options['bold'])) {
            $style->getFont()->setBold($options['bold']);
        }

        if (isset($options['size'])) {
            $style->getFont()->setSize($options['size']);
        }

        if (isset($options['horizontal'])) {
            $style->getAlignment()->setHorizontal($options['horizontal']);
        }

        $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function applyBorder($sheet, $range, $borderStyle)
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => $borderStyle,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);
    }

    private function setColumnWidths($sheet, $sessionCount)
    {
        $sheet->getColumnDimension('A')->setWidth(5);   // NO
        $sheet->getColumnDimension('B')->setWidth(12);  // NIM
        $sheet->getColumnDimension('C')->setWidth(34);  // NAMA
        $sheet->getStyle('C')->getAlignment()->setWrapText(true);
        $columnCount = $this->isPemicu()
            ? $this->groupPemicuSessions()->count() * 3
            : $sessionCount;
        // Set width untuk kolom sesi
        for ($i = 0; $i < $columnCount; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex(4 + $i);
            if ($this->isPemicu()) {
                $sheet->getColumnDimension($colLetter)->setWidth(10);
            } else {
                $sheet->getColumnDimension($colLetter)->setWidth(14);
            }
            $sheet->getStyle($colLetter)->getAlignment()->setWrapText(true);
        }
    }

    private function autoAdjustRowHeights($sheet, $lastColumn)
    {
        $highestRow = $sheet->getHighestRow();

        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('C' . $row)->getValue();

            if (!empty($cellValue)) {
                $colWidth = 30;
                $textLength = mb_strlen($cellValue);
                $lines = ceil($textLength / $colWidth);
                $calculatedHeight = max(23.25, 15 * $lines);

                $sheet->getRowDimension($row)->setRowHeight($calculatedHeight);
            }
        }

        $sheet->getStyle("A1:{$lastColumn}{$highestRow}")
            ->getAlignment();
    }

    private function isPemicu()
    {
        return strtolower($this->activityType) === 'pemicu';
    }

    private function isTBL()
    {
        return strtolower($this->activityType) === 'tbl';
    }

    private function groupPemicuSessions()
    {
        return $this->sessions
            ->groupBy(function ($s) {
                return floor($s->pemicu_ke / 10);
            })
            ->sortKeys();
    }
}
