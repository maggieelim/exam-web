<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RollbackCourseLecturerSeeder extends Seeder
{
    public function run()
    {
        DB::table('course_lecturer')->truncate();
    }
}
