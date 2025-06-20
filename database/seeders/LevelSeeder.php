<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Level;
use App\Models\Mistake;
class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'مبتدئ',
            'متوسط',
            'متقدم',
            'خبير'
        ];
        $levels = [];
        foreach ($names as $name) {
            $levels[] = Level::create(['name' => $name]);
        }
        $mistakes = Mistake::all();
        foreach ($levels as $level) {
            foreach ($mistakes as $mistake) {
                $level->mistakes()->attach($mistake->id, [
                    'value' => 1,
                ]);
            }
        }
    }
}