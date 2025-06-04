<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'المدير',
        ]);
        Role::create([
            'name' => 'استاذ',
        ]);
        Role::create([
            'name' => 'أستاذ مساعد',
        ]);
        Role::create([
            'name' => 'طالب',
        ]);
    }
}