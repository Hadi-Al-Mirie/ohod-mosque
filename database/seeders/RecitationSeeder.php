<?php

namespace Database\Seeders;

use App\Models\Recitation;
use App\Models\RecitationMistake;
use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use App\Models\Mistake;
use Illuminate\Database\Seeder;

class RecitationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $admins = User::whereIn('role_id', [1, 2])->get();
        $courses = Course::all();
        $mistakes = Mistake::all();

        if ($students->isEmpty() || $admins->isEmpty() || $courses->isEmpty() || $mistakes->isEmpty()) {
            $this->command->info("One or more required datasets (students, admins, courses, mistakes) are empty. Please seed them first.");
            return;
        }

        for ($i = 0; $i < 50; $i++) {
            $student = $students->random();
            $course = $courses->random();
            $admin = $admins->random();
            $page = random_int(1, 604);
            // Create a recitation record with a temporary perfect score of 100.
            $recitation = Recitation::create([
                'student_id' => $student->id,
                'by_id' => $admin->id,
                'course_id' => $course->id,
                'result' => 100,
                'page' => $page
            ]);

            $totalPenalty = 0;

            // For each mistake type, always create a RecitationMistake record.
            foreach ($mistakes as $mistake) {
                // Generate a random quantity between 0 and 3.
                $quantity = rand(0, 3);
                $totalPenalty += $mistake->value * $quantity;


            }

            // Calculate final result assuming a perfect score of 100.
            $calculatedResult = 100 - $totalPenalty;
            if ($calculatedResult < 0) {
                $calculatedResult = 0;
            }
            $recitation->update(['result' => $calculatedResult]);
        }

        $this->command->info("50 recitation records have been created with a RecitationMistake record for each mistake type.");
    }
}