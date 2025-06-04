<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;

class RegisterDailyAttendance extends Command
{
    protected $signature = 'attendance:register';
    protected $description = 'Registers daily attendance for all students.';

    public function handle()
    {
        $today = Carbon::today()->format('Y-m-d');

        // Get all students (not "users" if your model is Student)
        $students = Student::all();

        foreach ($students as $student) {
            $exists = Attendance::where('student_id', $student->id)
                ->whereDate('attendance_date', $today)
                ->exists();

            if (!$exists) {
                Attendance::create([
                    'student_id' => $student->id,
                    'value' => false, // or 0
                    'attendance_date' => $today,
                ]);
            }
        }

        $this->info("Attendance rows (value=0) created for $today");
        return 0;
    }
}