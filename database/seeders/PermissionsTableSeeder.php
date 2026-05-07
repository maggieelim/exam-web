<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('permissions')->delete();
        
        \DB::table('permissions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'view course',
                'guard_name' => 'web',
                'created_at' => '2025-09-11 04:39:11',
                'updated_at' => '2025-09-11 04:39:11',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'create course',
                'guard_name' => 'web',
                'created_at' => '2025-09-11 04:39:11',
                'updated_at' => '2025-09-11 04:39:11',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'edit course',
                'guard_name' => 'web',
                'created_at' => '2025-09-11 04:39:11',
                'updated_at' => '2025-09-11 04:39:11',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'delete course',
                'guard_name' => 'web',
                'created_at' => '2025-09-11 04:39:11',
                'updated_at' => '2025-09-11 04:39:11',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'view lecturer',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'create lecturer',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'edit lecturer',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'delete lecturer',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'view student',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'create student',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'edit student',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'delete student',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'manage schedule',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            13 => 
            array (
                'id' => 14,
                'name' => 'assign coordinator',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            14 => 
            array (
                'id' => 15,
                'name' => 'create exam',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            15 => 
            array (
                'id' => 16,
                'name' => 'edit exam',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            16 => 
            array (
                'id' => 17,
                'name' => 'publish exam',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
            17 => 
            array (
                'id' => 18,
                'name' => 'delete exam',
                'guard_name' => 'web',
                'created_at' => '2025-10-30 08:26:15',
                'updated_at' => '2025-10-30 08:26:15',
            ),
        ));
        
        
    }
}