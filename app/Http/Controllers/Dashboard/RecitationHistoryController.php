<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class RecitationHistoryController extends Controller
{
    public function show(Student $student)
    {
        try {
            $activeCourse = Student::activeCourse();

            // all student recitations keyed by page
            $all = $student->recitations()->get()->keyBy('page');
            // recitations in active course keyed by page
            $active = $student->recitations()
                ->where('course_id', $activeCourse->id)
                ->get()
                ->keyBy('page');

            // Build pages 1–604
            $rows = [];
            for ($i = 1; $i <= 604; $i++) {
                $rows[] = [
                    'page' => $i,
                    'recited' => isset($all[$i]),
                    'result' => isset($active[$i]) ? $active[$i]->calculateResult() : null,
                ];
            }

            return view('students.recitation_history', [
                'student' => $student,
                'activeCourse' => $activeCourse,
                'rows' => $rows,
            ]);
        } catch (\Exception $e) {
            Log::error('Recitation history error', ['id' => $student->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'Unable to load recitation history.');
        }
    }
}
