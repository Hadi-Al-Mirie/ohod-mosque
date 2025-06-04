<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceJustificationRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AttendanceJustificationRequestSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('ar_SA');

        // 1) Gather a few attendances of type = 2 (غياب غير مبرر)
        $attendances = Attendance::where('type_id', 2)
            ->inRandomOrder()
            ->limit(30)    // adjust how many you want
            ->get();

        if ($attendances->isEmpty()) {
            $this->command->info("No unexcused absences (type_id=2) found—nothing to seed.");
            return;
        }

        // 2) Pick some teacher/admin users as “requesters”
        $requesters = User::where('role_id', 2)->pluck('id')->all();
        if (empty($requesters)) {
            $this->command->error("No users with role_id 1 or 2 found—cannot assign requests.");
            return;
        }

        // 3) Create one justification request per attendance
        foreach ($attendances as $attendance) {
            AttendanceJustificationRequest::create([
                'attendance_id' => $attendance->id,
                'requested_by' => $faker->randomElement($requesters),
                'justification' => $faker->sentence(6),  // a short Arabic sentence
                'status' => 'pending',
            ]);
        }

        $this->command->info("✅ Seeded " . $attendances->count() . " attendance justification requests.");
    }
}