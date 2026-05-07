<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AcademicYearsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('academic_years')->delete();
        
        \DB::table('academic_years')->insert(array (
            0 => 
            array (
                'id' => 2,
                'year_name' => '2025/2026',
                'start_date' => '2025-07-01',
                'end_date' => '2026-07-30',
                'created_at' => '2026-01-26 16:40:48',
                'updated_at' => '2026-01-29 17:25:54',
            ),
            1 => 
            array (
                'id' => 5,
                'year_name' => '2026/2027',
                'start_date' => '2026-07-31',
                'end_date' => '2027-08-31',
                'created_at' => '2025-10-16 16:33:49',
                'updated_at' => '2025-10-20 16:00:07',
            ),
        ));
        
        
    }
}