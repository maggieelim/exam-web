<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SemestersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('semesters')->delete();
        
        \DB::table('semesters')->insert(array (
            0 => 
            array (
                'id' => 3,
                'academic_year_id' => 2,
                'semester_name' => 'Ganjil',
                'is_active' => 1,
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'created_at' => '2026-01-26 16:42:13',
                'updated_at' => '2026-01-26 16:43:25',
            ),
            1 => 
            array (
                'id' => 4,
                'academic_year_id' => 2,
                'semester_name' => 'Genap',
                'is_active' => 0,
                'start_date' => '2026-01-01',
                'end_date' => '2026-07-24',
                'created_at' => '2026-01-26 16:43:06',
                'updated_at' => '2026-01-29 17:25:54',
            ),
            2 => 
            array (
                'id' => 9,
                'academic_year_id' => 5,
                'semester_name' => 'Ganjil',
                'is_active' => 0,
                'start_date' => '2026-10-16',
                'end_date' => '2027-04-16',
                'created_at' => '2025-10-16 16:33:49',
                'updated_at' => '2025-12-03 15:44:22',
            ),
            3 => 
            array (
                'id' => 10,
                'academic_year_id' => 5,
                'semester_name' => 'Genap',
                'is_active' => 0,
                'start_date' => '2027-06-18',
                'end_date' => '2027-08-30',
                'created_at' => '2025-10-16 16:33:49',
                'updated_at' => '2025-12-03 15:44:22',
            ),
        ));
        
        
    }
}