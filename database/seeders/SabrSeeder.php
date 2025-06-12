<?php

namespace Database\Seeders;

use App\Models\Sabr;
use App\Models\SabrMistake;
use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use App\Models\Mistake;
use Illuminate\Database\Seeder;

class SabrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        // Using role_id 1, 2, 3 for sabr creators as per your requirement.
        $admins = User::whereIn('role_id', [1, 2, 3])->get();
        $courses = Course::all();
        $mistakes = Mistake::all();

        if ($students->isEmpty() || $courses->isEmpty() || $admins->isEmpty() || $mistakes->isEmpty()) {
            $this->command->info("One or more required datasets (students, courses, admins, mistakes) are empty. Please seed them first.");
            return;
        }

        for ($i = 0; $i < 50; $i++) {
            $student = $students->random();
            $course = $courses->random();
            $admin = $admins->random();
            $allJuz = range(1, 30);
            shuffle($allJuz);
            // Select a random number of juz between 1 and 30.
            $numSelected = rand(1, 30);
            $selectedJuz = array_slice($allJuz, 0, $numSelected);
            sort($selectedJuz); // Optional: sort for display clarity
            // Create a sabr record with a temporary perfect score of 100.
            $sabr = Sabr::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'by_id' => $admin->id,
                'result' => 100,
                'juz' => json_encode($selectedJuz),
            ]);
            $totalPenalty = 0;
            // For each mistake type, always create a SabrMistake record.
            foreach ($mistakes as $mistake) {
            }

            // Calculate the final result assuming a perfect score of 100.
            $calculatedResult = 100 - $totalPenalty;
            if ($calculatedResult < 0) {
                $calculatedResult = 0;
            }
            $sabr->update(['result' => $calculatedResult]);
        }

        $this->command->info("50 sabr records have been created with a SabrMistake record for each mistake type.");
    }
}
