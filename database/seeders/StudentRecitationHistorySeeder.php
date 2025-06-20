<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\ResultSetting;
use App\Models\Student;
use App\Models\Recitation;
use App\Models\Mistake;
use App\Models\MistakesRecorde;
use Illuminate\Support\Carbon;

class StudentRecitationHistorySeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // 1) Create two courses
            $oldCourse = Course::where('is_active', false)->first();
            $activeCourse = Course::where('is_active', true)->first();
            $student = Student::findOrFail(1);
            $recitationMistakes = Mistake::where('type', 'recitation')->get();
            if ($recitationMistakes->isEmpty()) {
                $this->command->error("No recitation mistakes defined!");
                return;
            }
            $firstMistakeId = $recitationMistakes->first()->id;

            // Helper to create one recitation + N mistake records
            $makeRec = function ($page, $courseId, bool $isFinal, int $penaltyCount) use ($student, $firstMistakeId) {
                $rec = Recitation::create([
                    'student_id' => $student->id,
                    'by_id' => 1,
                    'course_id' => $courseId,
                    'page' => $page,
                    'level_id' => $student->level_id,
                    'is_final' => $isFinal,
                ]);
                // create exactly $penaltyCount mistakes (each value=1)
                for ($i = 0; $i < $penaltyCount; $i++) {
                    MistakesRecorde::create([
                        'mistake_id' => $firstMistakeId,
                        'recitation_id' => $rec->id,
                        'sabr_id' => null,
                        'type' => 'recitation',
                        'page_number' => $page,
                        'line_number' => rand(1, 10),
                        'word_number' => rand(1, 5),
                    ]);
                }
                return $rec;
            };

            // Pages 1–7 in active course:
            // 1: highest raw=100  (0 mistakes), plus extra finals with errors
            $makeRec(1, $activeCourse->id, true, 0);
            for ($i = 0; $i < 3; $i++) {
                $makeRec(1, $activeCourse->id, true, rand(1, 20));
            }
            // 2: highest raw=75 → penalty=25
            for ($i = 0; $i < 4; $i++) {
                $makeRec(2, $activeCourse->id, true, rand(10, 30));
            }
            // ensure one has exactly 25
            $makeRec(2, $activeCourse->id, true, 25);
            // 3: one final with raw=50 → penalty=50
            $makeRec(3, $activeCourse->id, true, 50);
            // 4→7 each one rec + raw scores 90,75,70,60
            $makeRec(4, $activeCourse->id, true, 10);
            $makeRec(5, $activeCourse->id, true, 25);
            $makeRec(6, $activeCourse->id, true, 30);
            $makeRec(7, $activeCourse->id, true, 40);

            // Page 8 old course, many non‐final only
            for ($i = 0; $i < 3; $i++) {
                $makeRec(8, $oldCourse->id, false, rand(10, 40));
            }

            // Page 9 old course, many with one final
            for ($i = 0; $i < 2; $i++) {
                $makeRec(9, $oldCourse->id, false, rand(10, 40));
            }
            $makeRec(9, $oldCourse->id, true, rand(10, 40));

            // Page 10 old course, one final with raw=50
            $makeRec(10, $oldCourse->id, true, 50);

            $this->command->info("Seeded recitations for student #1 pages 1–10.");
        });
    }
}