<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('exams')->insert([
            [
                'course_id' => 1, // pastikan ada course id 1
                'exam_type_id' => 1, // UTS
                'exam_date' => '2025-10-20 09:00:00',
                'room' => 'Ruang A101',
                'duration' => 120,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'course_id' => 2,
                'exam_type_id' => 2, // UAS
                'exam_date' => '2025-12-10 13:00:00',
                'room' => 'Ruang B202',
                'duration' => 180,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
