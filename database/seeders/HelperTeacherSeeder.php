<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\HelperTeacher;
class HelperTeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => 'Helper Teacher ' . $i,
                'password' => bcrypt('password123'),
                'role_id' => 3,
            ]);

            HelperTeacher::create([
                'user_id' => $user->id,
                'phone' => '09' . rand(10000000, 99999999),
            ]);
        }
    }
}
