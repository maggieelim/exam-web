<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamQuestion;

class ExamQuestionSeeder extends Seeder
{
    public function run(): void
    {
        ExamQuestion::create([
            'exam_id' => 1, // pastikan exam_id=1 ada di tabel exams
            'question_text' => 'Apa ibukota Indonesia?',
            'question_type' => 'multiple_choice',
            'options' => json_encode(['Jakarta', 'Bandung', 'Surabaya', 'Medan']),
            'answer' => 'Jakarta',
        ]);

        ExamQuestion::create([
            'exam_id' => 1,
            'question_text' => 'Indonesia merdeka pada tahun berapa?',
            'question_type' => 'essay',
            'answer' => '1945',
        ]);

        ExamQuestion::create([
            'exam_id' => 1,
            'question_text' => 'Benar atau salah: Gunung Bromo ada di Jawa Timur.',
            'question_type' => 'true_false',
            'answer' => 'true',
        ]);
    }
}
