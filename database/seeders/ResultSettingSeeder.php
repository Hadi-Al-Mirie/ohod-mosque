<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ResultSetting;
class ResultSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['recitation', 'ممتاز', 90, 100, 5],
            ['recitation', 'جيد جداً', 75, 89, 4],
            ['recitation', 'جيد', 70, 74, 3],
            ['recitation', 'إعادة', 0, 69, 0],
            ['sabr', 'ممتاز', 90, 100, 5],
            ['sabr', 'جيد جداً', 75, 89, 4],
            ['sabr', 'جيد', 70, 74, 3],
            ['sabr', 'إعادة', 0, 69, 0],
        ];

        foreach ($settings as [$type, $name, $min, $max, $points]) {
            ResultSetting::create([
                'type' => $type,
                'name' => $name,
                'min_res' => $min,
                'max_res' => $max,
                'points' => $points,
            ]);
        }
    }
}