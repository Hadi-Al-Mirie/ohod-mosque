<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
class InfoController extends Controller
{
    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();
            $student = $user->student;
            if (!$student) {
                return response()->json(["message" => "failed to get the student"], 404);
            }
            $course = Student::activeCourse();
            $now = now();
            $oneWeekAgo = (clone $now)->subWeek();
            $twoWeeksAgo = (clone $now)->subWeeks(2);

            // Core metrics
            $workingDays = $student->workingDaysCount();
            $recitationsList = $student
                ->recitations()
                ->where('course_id', $course->id)
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'page' => $r->page,
                    'result' => $r->calculateResult(),
                ]);

            // --- Build the per-item sabrs array ---
            $sabrsList = $student
                ->sabrs()
                ->where('course_id', $course->id)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'juz' => $s->juz,               // already cast to array
                    'result' => $s->calculateResult(),
                ]);
            $recitationAvg = round($student->rawScores('recitation')->avg(), 2);

            $sabrAvg = round($student->rawScores('sabr')->avg(), 2);


            $recModels = $student
                ->recitations()
                ->where('course_id', $course->id)
                ->get();

            $sabrModels = $student
                ->sabrs()
                ->where('course_id', $course->id)
                ->get();

            // 2) Map to items including raw + result
            $recitationsList = $recModels->map(fn($r) => [
                'id' => $r->id,
                'page' => $r->page,
                'raw' => assessmentRawScore($r),
                'result' => $r->calculateResult(),
            ]);

            $sabrsList = $sabrModels->map(fn($s) => [
                'id' => $s->id,
                'juz' => $s->juz,
                'raw' => assessmentRawScore($s),
                'result' => $s->calculateResult(),
            ]);

            // 3) Compute averages directly from those lists
            $recitationAvg = round($recitationsList->avg('raw'), 2);
            $sabrAvg = round($sabrsList->avg('raw'), 2);



            $attendanceStats = $student->attendanceStats();
            // eager-load the creator relationship (so we can inspect their role)
            $notesList = $student
                ->notes()
                ->with('creator.role')
                ->latest('updated_at')
                ->get()
                ->map(fn($note) => [
                    'id' => $note->id,
                    // if the creator’s role is admin (role_id 1 or 2), show "الإدارة"
                    'by_name' => in_array($note->creator->role_id, [1, 2])
                        ? 'الإدارة'
                        : $note->creator->name,
                    'type' => $note->type === 'positive' ? 'إيجابية' : 'سلبية',
                    'status' => $note->status,
                    'value' => $note->value,
                    // only the date, in Y-m-d
                    'updated_at' => $note->updated_at->toDateString(),
                ]);


            // Rankings now & previous
            $rankNowCircle = $student->rankInCircle();
            $rankNowMosque = $student->rankInMosque();
            $rankPrevCircle = $student->rankInCircle($oneWeekAgo);
            $rankPrevMosque = $student->rankInMosque($oneWeekAgo);

            // Weekly deltas
            $gainThisWeek = $student->pointsDelta($oneWeekAgo);
            $gainLastWeek = $student->pointsDelta($twoWeeksAgo, $oneWeekAgo);
            $improvementPercent = percent_change($gainThisWeek, $gainLastWeek);



            return response()->json([
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                ],
                'course' => [
                    'id' => $course->id,
                    'name' => $course->name,
                ],
                'stats' => [
                    'working_days' => $workingDays,
                    'recitations' => [
                        'count' => $recitationsList->count(),
                        'average' => $recitationAvg,
                        'result_break' => $student->resultCounts('recitation'),
                        'points' => $student->recitationPoints(),
                        'items' => $recitationsList,
                    ],
                    'sabrs' => [
                        'count' => $sabrsList->count(),
                        'average' => $sabrAvg,
                        'result_break' => $student->resultCounts('sabr'),
                        'points' => $student->sabrPoints(),
                        'items' => $sabrsList,
                    ],
                    'attendance' => $attendanceStats,
                    'notes' => $notesList,
                ],
                'rankings' => [
                    'circle' => [
                        'now' => $rankNowCircle,
                        'prev' => $rankPrevCircle,
                    ],
                    'mosque' => [
                        'now' => $rankNowMosque,
                        'prev' => $rankPrevMosque,
                    ],
                ],
                'weekly' => [
                    'gain_this_week' => $gainThisWeek,
                    'gain_last_week' => $gainLastWeek,
                    'improvement_percent' => $improvementPercent,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Error showing student (API)', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Unable to fetch student data.',
            ], 500);
        }
    }
}