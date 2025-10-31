<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Semua permission akademik
        $permissions = [
            // Courses
            'view course',
            'create course',
            'edit course',
            'delete course',

            // Lecturer
            'view lecturer',
            'create lecturer',
            'edit lecturer',
            'delete lecturer',

            // Students
            'view student',
            'create student',
            'edit student',
            'delete student',

            // Jadwal
            'manage schedule',

            // Koordinator blok
            'assign coordinator',

            // Exam
            'create exam',
            'edit exam',
            'publish exam',
            'delete exam',
        ];

        // Buat permission jika belum ada
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        // === ADMIN ===
        // Admin = Kaprodi dalam konteks sistem akademik
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // === KOORDINATOR BLOK ===
        $koordinatorRole = Role::firstOrCreate(['name' => 'koordinator']);
        $koordinatorRole->syncPermissions([
            'view course',
            'create exam',
            'edit exam',
            'publish exam',
            'delete exam',
        ]);

        // === DOSEN BIASA ===
        $lecturerRole = Role::firstOrCreate(['name' => 'lecturer']);
        $lecturerRole->syncPermissions([
            'view course',
            'view student',
        ]);

      
        /*
        |--------------------------------------------------------------------------
        | Default User (Admin Akademik)
        |--------------------------------------------------------------------------
        */
    
    }
}
