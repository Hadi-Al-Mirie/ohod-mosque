<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class SabrHistoryController extends Controller
{
    public function show(Student $student)
    {
        try {
            $activeCourse = Student::activeCourse();

            // All sabr records for this student, any course
            $allSabr = $student->sabrs()->get();
            // Active‐course sabr
            $activeSabr = $student->sabrs()
                ->where('course_id', $activeCourse->id)
                ->get();

            // Build a map of juz => true for any sabr in any course
            $allMap = [];
            foreach ($allSabr as $s) {
                $juzArray = json_decode($s->juz, true) ?: [];
                foreach ($juzArray as $j) {
                    $allMap[$j] = true;
                }
            }

            // Build a map of juz => Sabr model for sabr in active course
            $activeMap = [];
            foreach ($activeSabr as $s) {
                $juzArray = json_decode($s->juz, true) ?: [];
                foreach ($juzArray as $j) {
                    $activeMap[$j] = $s;  // store the model for result lookup
                }
            }

            // Prepare rows for juz 1..30
            $rows = [];
            for ($j = 1; $j <= 30; $j++) {
                $rows[] = [
                    'juz' => $j,
                    'recited' => isset($allMap[$j]),
                    'result' => isset($activeMap[$j])
                        ? $activeMap[$j]->calculateResult()
                        : null,
                ];
            }

            return view('dashboard.students.sabr_history', compact('student', 'activeCourse', 'rows'));
        } catch (\Exception $e) {
            Log::error('Sabr history error', [
                'id' => $student->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()
                ->with('danger', 'تعذّر تحميل سجل السبر.');
        } catch (\Exception $e) {
            Log::error('Sabr history error', ['id' => $student->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'تعذّر تحميل سجل السبر.');
        }
    }
}
