<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
class InfoController extends Controller
{
    public function show(): JsonResponse
    {
        try {
            $student = $this->getAuthenticatedStudent();
            $course = Student::activeCourse();

            return response()->json([
                'student' => $this->studentPayload($student),
                'course' => $this->coursePayload($course),
                'stats' => $this->buildStats($student, $course),
                'rankings' => $this->buildRankings($student),
                'weekly' => $this->buildWeeklyDeltas($student),
            ]);
        } catch (Exception $e) {
            Log::error('Error showing student (API)', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Unable to fetch student data.'], 500);
        }
    }

    private function getAuthenticatedStudent(): Student
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // explicitly resolve the relation to the model:
        $student = $user->student()->first();

        if (!$student) {
            abort(404, 'No student record found.');
        }

        return $student;
    }

    private function studentPayload(Student $student): array
    {
        return [
            'id' => $student->id,
            'name' => $student->user->name,
            'level' => $student->level->name
        ];
    }

    private function coursePayload(Course $course): array
    {
        return [
            'id' => $course->id,
            'name' => $course->name,
        ];
    }

    private function buildStats(Student $student, Course $course): array
    {
        $working_days = $student->workingDaysCount();
        $recitations = $this->buildAssessmentSection($student, 'recitation', $course);
        $sabrs = $this->buildAssessmentSection($student, 'sabr', $course);
        $attendance = $student->attendanceStats();
        $attendanceList = $this->buildAttendanceList($student, $course);
        $notes = $this->buildNotesList($student);

        return compact('working_days', 'recitations', 'attendanceList', 'sabrs', 'attendance', 'notes');
    }

    private function buildAssessmentSection(Student $student, string $type, Course $course): array
    {
        // use the same pattern for recitations and sabrs
        $relationName = $type === 'recitation' ? 'recitations' : 'sabrs';
        $rawScores = $student->rawScores($type);
        $models = $student->{$relationName}()->where('course_id', $course->id)->get();

        // items with raw + result
        $items = $models->map(fn($m) => [
            'id' => $m->id,
            $type === 'recitation' ? 'page' : 'juz' => $m->{$type === 'recitation' ? 'page' : 'juz'},
            'raw' => assessmentRawScore($m),
            'result' => $m->calculateResult(),
        ]);

        return [
            'count' => $items->count(),
            'average' => round($items->avg('raw'), 2),
            'result_break' => $student->resultCounts($type),
            'points' => $type === 'recitation'
                ? $student->recitationPoints()
                : $student->sabrPoints(),
            'items' => $items,
        ];
    }

    private function buildAttendanceList(Student $student, Course $course): array
    {
        return $student->attendances()
            ->where('course_id', $course->id)
            ->with('type')
            ->orderBy('attendance_date', 'asc')
            ->get()
            ->map(fn($att) => [
                'date' => $att->attendance_date->toDateString(),
                'attendanceType' => $att->type->name,
            ])
            ->toArray();
    }
    private function buildNotesList(Student $student): \Illuminate\Support\Collection
    {
        return $student->notes()
            ->with('creator.role')
            ->latest('updated_at')
            ->get()
            ->map(fn($note) => [
                'id' => $note->id,
                'by_name' => in_array($note->creator->role_id, [1, 2])
                    ? 'الإدارة'
                    : $note->creator->name,
                'type' => $note->type === 'positive' ? 'إيجابية' : 'سلبية',
                'status' => $note->status,
                'value' => $note->value,
                'updated_at' => $note->updated_at->toDateString(),
            ]);
    }

    private function buildRankings(Student $student): array
    {
        $oneWeekAgo = now()->subWeek();
        return [
            'circle' => [
                'now' => $student->rankInCircle(),
                'prev' => $student->rankInCircle($oneWeekAgo),
            ],
            'mosque' => [
                'now' => $student->rankInMosque(),
                'prev' => $student->rankInMosque($oneWeekAgo),
            ],
        ];
    }

    private function buildWeeklyDeltas(Student $student): array
    {
        $now = now();
        $oneWeek = (clone $now)->subWeek();
        $twoWeeks = (clone $now)->subWeeks(2);
        $gainThis = $student->pointsDelta($oneWeek);
        $gainLast = $student->pointsDelta($twoWeeks, $oneWeek);
        $percent = percent_change($gainThis, $gainLast);

        return [
            'gain_this_week' => $gainThis,
            'gain_last_week' => $gainLast,
            'improvement_percent' => $percent,
        ];
    }
}
