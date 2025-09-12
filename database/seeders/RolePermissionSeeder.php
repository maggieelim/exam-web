<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar permission
        $permissions = [
            'view course',
            'create course',
            'edit course',
            'delete course',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Role Admin (punya semua permission)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Role Lecturer
        $lecturerRole = Role::firstOrCreate(['name' => 'lecturer']);
        $lecturerRole->givePermissionTo([
            'view course',
            'create course',
            'edit course',
            'delete course',
        ]);

        // Role Student
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $studentRole->givePermissionTo([
            'view course',
        ]);

        // Membuat user super admin
        $user = User::firstOrCreate(
            ['email' => 'maggielim1999@gmail.com'],
            [
                'name' => 'Maggie',
                'password' => bcrypt('123456'),
            ]
        );

        $user->assignRole($adminRole);
    }
}
