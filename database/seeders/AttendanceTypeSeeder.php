<?php

namespace Database\Seeders;

use App\Models\AttendanceType;
use Illuminate\Database\Seeder;

class AttendanceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = ['حضور', 'غياب غير مبرر', 'غياب مبرر', 'تأخير'];
        foreach ($types as $type) {
            AttendanceType::firstOrCreate(['name' => $type]);
        }
    }
}