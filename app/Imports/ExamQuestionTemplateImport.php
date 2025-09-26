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
        $category = ExamQuestionCategory::firstOrCreate(
            ['exam_id' => $this->examId, 'name' => $categoryName],
            ['created_at' => now(), 'updated_at' => now()]
        );
        // Simpan soal
        $question = new ExamQuestion([
            'exam_id'       => $this->examId,
            'category_id'      => $category->id,
            'badan_soal'    => $row[2],
            'kalimat_tanya' => $row[3],
            'kode_soal'     => $this->generateKodeSoal(),
            'created_by'    => Auth::id(),
            'updated_by'    => Auth::id(),
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
            if (!empty($text)) {
                ExamQuestionAnswer::create([
                    'exam_question_id' => $question->id,
                    'option'           => $opt,
                    'text'             => $text,
                    'is_correct'       => in_array($opt, $correctAnswers),
                ]);
            }
        }

        return $question;
    }

    private function generateKodeSoal()
    {
        $lastSoal = ExamQuestion::where('exam_id', $this->examId)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastSoal) {
            $lastNumber = (int) substr($lastSoal->kode_soal, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'SWG-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
