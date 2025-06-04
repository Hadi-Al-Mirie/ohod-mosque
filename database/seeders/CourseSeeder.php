<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Course::create([
            'name' => 'الأولى',
            'start_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
            'end_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
            'working_days' => json_encode([0, 1, 2, 3, 4]),
            'is_active' => true,
        ]);
    }
}
