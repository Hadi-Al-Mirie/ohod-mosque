<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;

class UpdateCashedPoints extends Command
{
    protected $signature = 'students:update-points';
    protected $description = 'Recompute and cache points for all students';

    public function handle()
    {
        $this->info('Starting to update cashed_points for all students…');

        Student::chunk(100, function ($students) {
            foreach ($students as $student) {
                $student->update(['cashed_points' => $student->points]);
                $this->info("updated cashed_points  {$student->points}");
            }
        });
        return 0;
    }
}
