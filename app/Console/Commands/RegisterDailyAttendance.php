<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;

class RegisterDailyAttendance extends Command
{
    protected $signature = 'attendance:register';
    protected $description = 'Registers daily attendance (type_id=2) for all students in the active course working day.';

    public function handle()
    {
        $course = Course::where('is_active', true)->first();
        if (!$course) {
            $this->info('No active course found—skipping attendance registration.');
            return 0;
        }
        $workingDays = json_decode($course->working_days, true) ?: [];
        $today = Carbon::today();
        $weekday = $today->dayOfWeek;
        $todayString = $today->format('Y-m-d');
        if (!in_array($weekday, $workingDays, true)) {
            $this->info("Today ({$todayString}) is not a working day for Course #{$course->id}—skipping.");
            return 0;
        }
        $admin = User::where('role_id', 1)->first();
        if (!$admin) {
            $this->error('No admin user found (role_id=1). Cannot set by_id.');
            return 1;
        }
        $typeId = 2;
        $students = Student::all();
        $count = 0;
        foreach ($students as $student) {
            $exists = Attendance::where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->whereDate('attendance_date', $todayString)
                ->exists();
            if (!$exists) {
                Attendance::create([
                    'student_id' => $student->id,
                    'by_id' => $admin->id,
                    'course_id' => $course->id,
                    'type_id' => $typeId,
                    'justification' => null,
                    'attendance_date' => $todayString,
                ]);
                $count++;
            }
        }
        $this->info("Registered attendance for {$count} students on {$todayString} (type_id={$typeId}).");
        return 0;
    }
}
