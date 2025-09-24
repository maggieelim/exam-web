<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Buat user baru
        $userId = DB::table('users')->insertGetId([
            'name' => 'Mag',
            'email' => 'moonglitzzz@gmail.com',
            'password' => Hash::make('12345678'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Pastikan role admin ada
        $role = Role::firstOrCreate(['name' => 'lecturer']);

        // Assign role admin ke user
        DB::table('model_has_roles')->insert([
            'role_id' => $role->id,
            'model_type' => 'App\\Models\\User',
            'model_id' => $userId
        ]);
    }
}
