<?php

namespace Database\Seeders;

use App\Models\AttendanceType;
use Illuminate\Database\Seeder;

class AttendanceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = ['حضور' => 5, 'غياب غير مبرر' => -2, 'غياب مبرر' => 0, 'تأخير' => -1];
        foreach ($types as $name => $value) {
            AttendanceType::updateOrCreate(
                ['name' => $name],
                ['value' => $value]
            );
        }
    }
}
