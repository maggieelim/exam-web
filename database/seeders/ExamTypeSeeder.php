<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('exam_types')->insert([
            ['name' => 'UTS', 'description' => 'Ujian Tengah Semester', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'UAS', 'description' => 'Ujian Akhir Semester', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quiz', 'description' => 'Ujian singkat / kuis', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Remedial', 'description' => 'Ujian perbaikan nilai', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
