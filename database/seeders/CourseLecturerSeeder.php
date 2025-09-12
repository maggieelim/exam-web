<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;

class CourseLecturerSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua course
        $courses = Course::all();

        // Ambil semua dosen (asumsi role 'lecturer')
        $lecturers = User::role('lecturer')->get();

        foreach ($courses as $course) {
            // Pilih 1 atau beberapa dosen secara random untuk setiap course
            $assignedLecturers = $lecturers->random(2); // bisa random(2) untuk lebih dari 1 dosen

            foreach ($assignedLecturers as $lecturer) {
                // attach dosen ke course
                $course->lecturers()->attach($lecturer->id);
            }
        }
    }
}
