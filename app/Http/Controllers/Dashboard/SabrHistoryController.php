<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\ResultSetting;
class SabrHistoryController extends Controller
{
    public function show(Student $student)
    {
        try {
            $activeCourse = Student::activeCourse();

            // 1) Fetch all sabr records (any course)
            $allSabr = $student->sabrs()->get();

            // 2) Fetch only active-course sabrs
            $activeSabr = $allSabr->where('course_id', $activeCourse->id);

            // 3) Group previous-course sabrs by juz
            $prevByJuz = [];
            foreach ($allSabr->where('course_id', '!=', $activeCourse->id) as $s) {
                foreach (json_decode($s->juz, true) ?: [] as $j) {
                    $prevByJuz[$j][] = $s;
                }
            }

            // 4) Group active-course sabrs by juz
            $activeByJuz = [];
            foreach ($activeSabr as $s) {
                foreach (json_decode($s->juz, true) ?: [] as $j) {
                    $activeByJuz[$j][] = $s;
                }
            }

            // 5) Load sabr ResultSettings (ID 5 best → 8 worst)
            $settings = ResultSetting::where('type', 'sabr')
                ->orderBy('id')
                ->get();

            $rows = [];
            for ($j = 1; $j <= 30; $j++) {
                $displayDone = false;
                $displayRes = null;

                // First, check active-course attempts
                if (!empty($activeByJuz[$j])) {
                    // Pick best active setting
                    $best = null;
                    foreach ($activeByJuz[$j] as $sabr) {
                        $raw = assessmentRawScore($sabr);
                        $setting = $settings->first(fn($s) => $raw >= $s->min_res && $raw <= $s->max_res);
                        if ($setting && ($best === null || $setting->id < $best->id)) {
                            $best = $setting;
                        }
                    }
                    if ($best && $best->id !== 8) {
                        $displayDone = true;
                        $displayRes = $best->name;
                    }
                }
                // Otherwise, check previous-course attempts—but only if any prior attempt wasn't in bracket 8
                elseif (!empty($prevByJuz[$j])) {
                    $bestPrev = null;
                    foreach ($prevByJuz[$j] as $sabr) {
                        $raw = assessmentRawScore($sabr);
                        $setting = $settings->first(fn($s) => $raw >= $s->min_res && $raw <= $s->max_res);
                        if ($setting && ($bestPrev === null || $setting->id < $bestPrev->id)) {
                            $bestPrev = $setting;
                        }
                    }
                    if ($bestPrev && $bestPrev->id !== 8) {
                        $displayDone = true;
                    }
                }

                $rows[] = [
                    'juz' => $j,
                    'recited' => $displayDone,
                    'result' => $displayRes,
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
        }
    }
}
