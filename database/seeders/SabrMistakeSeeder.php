<?php

namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sabr;
use App\Models\SabrMistake;
use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use App\Models\Mistake;
class SabrMistakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sabrs = Sabr::all();
        $students = Student::all();
        $admins = User::whereIn('role_id', [1, 2])->get();
        $mistakes = Mistake::all();
        if ($sabrs->isEmpty() || $students->isEmpty() || $admins->isEmpty() || $mistakes->isEmpty()) {
            $this->command->info("One or more required datasets (sabrs, students, admins, mistakes) are empty. Please seed them first.");
            return;
        }
        foreach ($sabrs as $sabr) {
            $mistakeCount = rand(0, 3);
            $selectedMistakes = $mistakes->random($mistakeCount);
            foreach ($selectedMistakes as $mistake) {

            }
        }
    }
}