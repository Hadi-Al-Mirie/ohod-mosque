<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\ResultSetting;
class RecitationHistoryController extends Controller
{
    public function show(Student $student)
    {
        try {
            $activeCourse = Student::activeCourse();
            $rows = $student->recitationHistoryRows();

            return view(
                'dashboard.students.recitation_history',
                compact('student', 'activeCourse', 'rows')
            );
        } catch (\Exception $e) {
            Log::error('Recitation history error', [
                'id' => $student->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()
                ->with('danger', 'تعذّر تحميل سجل التسميع.');
        }
    }


}
