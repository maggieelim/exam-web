<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(DifficultyLevelsTableSeeder::class);
        $this->call(AcademicYearsTableSeeder::class);
        $this->call(ActivitiesTableSeeder::class);
        $this->call(CoursesTableSeeder::class);
        $this->call(SemestersTableSeeder::class);
    }
}
