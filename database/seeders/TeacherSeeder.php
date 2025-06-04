<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'ا',
            'password' => Hash::make('ا'),
            'role_id' => 2,
        ]);
        Teacher::create([
            'user_id' => $user->id,
            'phone' => '0987654321',
            'circle_id' => 1,
        ]);
        $user = User::create([
            'name' => 'خالد',
            'password' => Hash::make('password'),
            'role_id' => 2,
        ]);
        Teacher::create([
            'user_id' => $user->id,
            'phone' => '0987654321',
            'circle_id' => 2,
        ]);
        $user = User::create([
            'name' => 'سمير',
            'password' => Hash::make('12345678'),
            'role_id' => 2,
        ]);
        Teacher::create([
            'user_id' => $user->id,
            'phone' => '0987654321',
            'circle_id' => 3,
        ]);
    }
}
