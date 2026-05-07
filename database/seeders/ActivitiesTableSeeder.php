<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ActivitiesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('activities')->delete();
        
        \DB::table('activities')->insert(array (
            0 => 
            array (
                'id' => 1,
                'code' => 'K',
                'activity_name' => 'Kuliah',
                'category' => 'teaching',
            ),
            1 => 
            array (
                'id' => 2,
                'code' => 'SL',
                'activity_name' => 'Skill Lab',
                'category' => 'teaching',
            ),
            2 => 
            array (
                'id' => 3,
                'code' => 'PR',
                'activity_name' => 'Praktikum',
                'category' => 'teaching',
            ),
            3 => 
            array (
                'id' => 4,
                'code' => 'P',
                'activity_name' => 'Pleno',
                'category' => 'teaching',
            ),
            4 => 
            array (
                'id' => 5,
                'code' => 'T',
                'activity_name' => 'Pemicu',
                'category' => 'teaching',
            ),
            5 => 
            array (
                'id' => 6,
                'code' => 'U',
                'activity_name' => 'Ujian Teori',
                'category' => 'exam',
            ),
            6 => 
            array (
                'id' => 7,
                'code' => 'UP',
                'activity_name' => 'Ujian Praktikum',
                'category' => 'exam',
            ),
            7 => 
            array (
                'id' => 8,
                'code' => 'USL',
                'activity_name' => 'Ujian Skill Lab',
                'category' => 'exam',
            ),
        ));
        
        
    }
}