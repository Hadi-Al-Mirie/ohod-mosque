<?php

namespace Database\Seeders;

use App\Models\{
    User,
    Student,
    Course,
    Level,
    Mistake,
    Attendance,
    AttendanceType,
    Recitation,
    Sabr,
    MistakesRecorde,
    Note
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Integer;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;


class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $arfaker = Faker::create('ar_SA');
        $circleId = 1;
        $totalStudents = 21;

        // Fetch only recitation and sabr mistakes separately
        $recitationMistakes = Mistake::where('type', 'recitation')->get();
        $sabrMistakes = Mistake::where('type', 'sabr')->get();

        $adminForRec = User::whereIn('role_id', [1, 2])->get();
        $adminForSabr = User::whereIn('role_id', [1, 2, 3])->get();
        $admin = User::where('role_id', 1)->first();
        $activeCourse = Course::where('is_active', true)->first();

        $levels = Level::pluck('id')->toArray();
        if (empty($levels) || !$admin || !$activeCourse) {
            $this->command->error("Missing levels, admin or active course!");
            return;
        }

        $workingDays = json_decode($activeCourse->working_days, true) ?: [];

        for ($i = 1; $i <= $totalStudents; $i++) {
            // Create user & student
            $user = User::create([
                'name' => $arfaker->firstName . ' ' . $arfaker->lastName,
                'role_id' => 4,
                'password' => Hash::make('password'),
            ]);

            do {
                $token = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            } while (Student::where('qr_token', $token)->exists());

            $levelId = $faker->randomElement($levels);

            $student = Student::create([
                'student_phone' => $faker->phoneNumber,
                'father_phone' => $faker->phoneNumber,
                'location' => $faker->address,
                'birth' => $faker->date('Y-m-d'),
                'class' => $faker->randomElement(['Class 1', 'Class 2', 'Class 3']),
                'school' => $faker->company,
                'father_name' => $faker->name('male'),
                'father_job' => $faker->jobTitle,
                'mother_name' => $faker->name('female'),
                'mother_job' => $faker->jobTitle,
                'user_id' => $user->id,
                'circle_id' => $circleId,
                'qr_token' => $token,
                'level_id' => $levelId,
            ]);

            if ($i % 7 === 0) {
                $circleId++;
            }

            // Build the period
            $start = Carbon::parse($activeCourse->start_date)->startOfDay();
            $endDate = Carbon::parse($activeCourse->end_date)->endOfDay();
            $today = now()->startOfDay();
            $end = $endDate->lessThan($today) ? $endDate : $today;
            $period = CarbonPeriod::create($start, $end);

            // For each working day: attendance + recitations
            foreach ($period as $day) {
                if (!in_array($day->dayOfWeek, $workingDays))
                    continue;

                // Attendance
                $attTypeId = AttendanceType::pluck('id')->random();
                Attendance::create([
                    'student_id' => $student->id,
                    'course_id' => $activeCourse->id,
                    'type_id' => $attTypeId,
                    'justification' => $attTypeId === 3 ? 'تم تقديم تقرير طبي' : null,
                    'by_id' => $adminForRec->random()->id,
                    'attendance_date' => $day->format('Y-m-d'),
                ]);

                // 1–2 Recitations per day
                foreach (range(1, rand(1, 2)) as $_) {
                    $lvl = $student->level;
                    $rec = Recitation::create([
                        'student_id' => $student->id,
                        'by_id' => $adminForRec->random()->id,
                        'course_id' => $activeCourse->id,
                        'page' => rand(1, 604),
                        'level_id' => $levelId
                    ]);

                    $penalty = 0;


                    foreach ($recitationMistakes as $m) {
                        $qty = rand(0, 3);
                        $pivot = optional(
                            $lvl->mistakes()
                                ->where('mistakes.id', $m->id)
                                ->first()
                        )->pivot;
                        $val = $pivot->value ?? 0;
                        $penalty += $val * $qty;

                        MistakesRecorde::create([
                            'mistake_id' => $m->id,
                            'recitation_id' => $rec->id,
                            'sabr_id' => null,
                            'type' => 'recitation',
                            'quantity' => $qty,
                        ]);
                    }
                }
            }

            // Sabr on ~30% of working days
            $workDaysArr = iterator_to_array($period);
            $pickCount = ceil(count($workDaysArr) * 0.3);
            $datesToPick = (array) array_rand($workDaysArr, $pickCount);

            foreach ($datesToPick as $idx) {
                $day = $workDaysArr[$idx];
                if (!in_array($day->dayOfWeek, $workingDays))
                    continue;

                $sabr = Sabr::create([
                    'student_id' => $student->id,
                    'by_id' => $adminForSabr->random()->id,
                    'course_id' => $activeCourse->id,
                    'juz' => json_encode(range(1, rand(1, 30))),
                    'level_id' => $levelId
                ]);

                $penalty = 0;
                $lvl = $student->level;

                foreach ($sabrMistakes as $m) {
                    $qty = rand(0, 3);
                    $pivot = optional(
                        $lvl->mistakes()
                            ->where('mistakes.id', $m->id)
                            ->first()
                    )->pivot;
                    $val = $pivot->value ?? 0;
                    $penalty += $val * $qty;

                    MistakesRecorde::create([
                        'mistake_id' => $m->id,
                        'recitation_id' => null,
                        'sabr_id' => $sabr->id,
                        'type' => 'sabr',
                        'quantity' => $qty,
                    ]);
                }
            }
            $student->update(['cashed_points' => $student->points]);
            // One random note
            $type = $faker->randomElement(['positive', 'negative']);
            Note::create([
                'by_id' => $admin->id,
                'student_id' => $student->id,
                'course_id' => $activeCourse->id,
                'reason' => 'ملاحظة تلقائية ' . $arfaker->sentence(1),
                'type' => $type,
                'value' => $faker->numberBetween(5, 50) * ($type === 'negative' ? -1 : 1),
                'status' => 'approved',
            ]);

            // Refresh cached points
            $student->update(['cashed_points' => $student->points]);
        }

        $this->command->info("✅ Created $totalStudents students with full records!");
    }
}