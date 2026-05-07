<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('roles')->delete();
        
        \DB::table('roles')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'admin',
                'guard_name' => 'web',
                'created_at' => '2025-09-11 04:39:11',
                'updated_at' => '2025-09-11 04:39:11',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'lecturer',
                'guard_name' => 'web',
                'created_at' => '2025-09-11 04:39:11',
                'updated_at' => '2025-09-11 04:39:11',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'student',
                'guard_name' => 'web',
                'created_at' => '2025-09-11 04:39:11',
                'updated_at' => '2025-09-11 04:39:11',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'koordinator',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
        ));
        
        
    }
}