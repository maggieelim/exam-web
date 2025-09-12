<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('exam_answers')->insert([
            [
                'exam_id' => 1,
                'exam_question_id' => 1,
                'user_id' => 2, // contoh peserta
                'answer' => 'B',
                'is_correct' => true,
                'score' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'exam_id' => 1,
                'exam_question_id' => 2,
                'user_id' => 2,
                'answer' => 'Paris',
                'is_correct' => false,
                'score' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
