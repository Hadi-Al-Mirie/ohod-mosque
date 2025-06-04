<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceType;
use App\Models\Student;
use App\Models\Course;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $attendanceTypes = AttendanceType::pluck('id', 'name')->all();
        $students = Student::all();
        $courses = Course::all();
        if ($students->isEmpty() || $courses->isEmpty() || empty($attendanceTypes)) {
            $this->command->info("No students, courses, or attendance types found. Please seed them first.");
            return;
        }
        for ($i = 0; $i < 50; $i++) {
            $student = $students->random();
            $course = $courses->random();
            $randomTypeName = array_rand($attendanceTypes);
            $typeId = $attendanceTypes[$randomTypeName];
            $jus = null;
            if ($typeId == 3)
                $jus = "مرض/أسباب شخصية";
            Attendance::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'type_id' => $typeId,
                'justification' => $jus,
                'attendance_date' => Carbon::now()->subDays(rand(0, 30)),
            ]);
        }
        $this->command->info("50 attendance records have been created.");
    }
}
