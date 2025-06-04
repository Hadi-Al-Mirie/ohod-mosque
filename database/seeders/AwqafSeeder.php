<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Awqaf;
use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Arr;

class AwqafSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('ar_SA');

        // تأكد من وجود دورة نشطة
        $course = Course::where('is_active', true)->first();
        if (!$course) {
            $this->command->warn('لم يتم العثور على دورة نشطة، تخطّي Seeder الأوقاف.');
            return;
        }

        // اختر بعض الطلاب
        $students = Student::all();
        if ($students->isEmpty()) {
            $this->command->warn('لا يوجد طلاب في قاعدة البيانات، تخطّي Seeder الأوقاف.');
            return;
        }

        // اختر مستخدماً للتسجيل (مثلاً المشرف الأول)
        $creator = User::where('role_id', 1)->first()
            ?? User::first();

        // نولّد 20 سجلّاً عيّناً (أو حسب عدد الطلاب إن قل)
        for ($i = 0; $i < 20; $i++) {
            $student = $students->random();

            Awqaf::create([
                'student_id' => $student->id,
                'by_id' => $creator->id,
                'course_id' => $course->id,
                'juz' => collect(range(1, 30))->shuffle()->take(rand(1, 3))->values()->all(),
                'level_id' => $student->level_id,
                'type' => 'nomination',
            ]);
        }

        $this->command->info('تم إنشاء ' . Awqaf::count() . ' سجلّ أوْقاف.');
    }
}
