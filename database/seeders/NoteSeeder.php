<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Note;
use Nette\Utils\Random;
use Faker\Factory as Faker;
class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = User::where('role_id', 2)->get()->pluck('id');
        $students = Student::all()->pluck('id');
        $arfaker = Faker::create('ar_SA');
        $cid = course_id();
        $faker = Faker::create();
        $arabicReasons = [
            'إهمال في الواجبات',
            'تفوق في الاختبارات',
            'سلوك غير لائق',
            'مشاركة فعالة',
            'تأخر متكرر',
            'تميز أكاديمي',
            'عدم انضباط',
            'إبداع ملحوظ',
            'غياب بدون عذر',
            'سبر عشر أجزاء'
        ];
        for ($i = 0; $i < 9; $i++) {
            $type = $faker->randomElement(['positive', 'negative']);
            Note::create([
                'by_id' => $admins->random(),
                'student_id' => $students->random(),
                'course_id' => $cid,
                'reason' => $arfaker->randomElement($arabicReasons),
                'type' => $type,
            ]);
        }
    }
}
