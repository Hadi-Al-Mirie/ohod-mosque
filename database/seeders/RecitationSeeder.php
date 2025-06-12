<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recitation;
use App\Models\MistakesRecorde;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;

class RecitationSeeder extends Seeder
{
    public function run(): void
    {
        // assume student #1 exists
        $student = Student::findOrFail(1);

        // pick an instructor/admin for by_id
        $byId = User::whereIn('role_id', [1, 2, 3])->first()->id;

        // active course
        $courseId = Course::where('is_active', true)->value('id');

        // common data
        $data = [
            'student_id' => $student->id,
            'by_id' => $byId,
            'course_id' => $courseId,
            'page' => 50,
            'level_id' => $student->level_id,
            'is_final' => false,
        ];

        // 1) First recitation, with two mistake_records (mistake_id = 4)
        $rec1 = Recitation::create($data);
        for ($i = 0; $i < 2; $i++) {
            MistakesRecorde::create([
                'mistake_id' => 4,
                'recitation_id' => $rec1->id,
                'sabr_id' => null,
                'type' => 'recitation',
                'page_number' => 50,
                'line_number' => rand(1, 30),
                'word_number' => rand(1, 15),
            ]);
        }

        // 2) Second recitation, same page, but no mistakes attached
        $rec2 = Recitation::create($data);
        for ($i = 0; $i < 2; $i++) {
            MistakesRecorde::create([
                'mistake_id' => 5,
                'recitation_id' => $rec2->id,
                'sabr_id' => null,
                'type' => 'recitation',
                'page_number' => 50,
                'line_number' => rand(1, 30),
                'word_number' => rand(1, 15),
            ]);
        }
    }
}