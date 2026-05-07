<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DifficultyLevelsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('difficulty_levels')->delete();
        
        \DB::table('difficulty_levels')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Easy',
                'min_ratio' => '0.751',
                'max_ratio' => '1.000',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Medium',
                'min_ratio' => '0.201',
                'max_ratio' => '0.750',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 => 
            array (
                'id' => 4,
                'name' => 'Hard',
                'min_ratio' => '0.000',
                'max_ratio' => '0.200',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}