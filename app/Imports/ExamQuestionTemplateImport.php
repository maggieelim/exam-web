<?php

namespace App\Imports;

use App\Models\ExamQuestion;
use App\Models\ExamQuestionAnswer;
use App\Models\ExamQuestionCategory;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;

class ExamQuestionTemplateImport implements ToModel
{
    protected $examId;

    public function __construct($examId)
    {
        $this->examId = $examId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Lewati header
        if ($row[0] === 'No' || empty($row[1])) {
            return null;
        }

        $categoryName = trim($row[1]);
        $category = ExamQuestionCategory::firstOrCreate(['exam_id' => $this->examId, 'name' => $categoryName], ['created_at' => now(), 'updated_at' => now()]);
        // Simpan soal
        $question = new ExamQuestion([
            'exam_id' => $this->examId,
            'category_id' => $category->id,
            'badan_soal' => $row[2],
            'kalimat_tanya' => $row[3],
            'kode_soal' => $this->generateKodeSoal(),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
        $question->save();

        // Jawaban benar bisa lebih dari satu (misal: "B,C")
        $correctAnswers = array_map('trim', explode(',', strtoupper($row[9])));

        // Simpan opsi A–E
        $options = [
            'A' => $row[4],
            'B' => $row[5],
            'C' => $row[6],
            'D' => $row[7],
            'E' => $row[8] ?? null,
        ];

        foreach ($options as $opt => $text) {
            if (isset($text) && $text !== '') {
                ExamQuestionAnswer::create([
                    'exam_question_id' => $question->id,
                    'option' => $opt,
                    'text' => $text,
                    'is_correct' => in_array($opt, $correctAnswers),
                ]);
            }
        }

        return $question;
    }

    private function generateKodeSoal()
    {
        // Prefix bebas
        $prefix = 'Q-';

        // Karakter yang dipakai (A–Z, 0–9)
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        // Panjang kode (misalnya 6)
        $random = substr(str_shuffle(str_repeat($characters, 6)), 0, 6);

        $kode = $prefix . $random;

        // Pastikan unik di exam_id ini
        while (ExamQuestion::where('exam_id', $this->examId)->where('kode_soal', $kode)->exists()) {
            $random = substr(str_shuffle(str_repeat($characters, 6)), 0, 6);
            $kode = $prefix . $random;
        }

        return $kode;
    }
}
