<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CircleSeeder::class,
            CourseSeeder::class,
            UserSeeder::class,
            TeacherSeeder::class,
            AttendanceTypeSeeder::class,
            MistakesSeeder::class,
            LevelSeeder::class,
            StudentSeeder::class,
            ResultSettingSeeder::class,
            AttendanceJustificationRequestSeeder::class,
            AwqafSeeder::class
        ]);
    }
}
