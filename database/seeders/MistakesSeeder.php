<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mistake;
class MistakesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mistake::create([
            'name' => 'تشكيل',
            'type' => 'sabr',
        ]);
        Mistake::create([
            'name' => 'حفظ',
            'type' => 'sabr',
        ]);
        Mistake::create([
            'name' => 'تجويد',
            'type' => 'sabr',
        ]);
        Mistake::create([
            'name' => 'تشكيل',
            'type' => 'recitation',
        ]);
        Mistake::create([
            'name' => 'حفظ',
            'type' => 'recitation',
        ]);
        Mistake::create([
            'name' => 'تجويد',
            'type' => 'recitation',
        ]);
    }
}