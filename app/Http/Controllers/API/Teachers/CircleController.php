<?php

namespace App\Http\Controllers\API\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Auth;
use Illuminate\Http\Request;
use App\Models\Circle;
use App\Models\Attendance;
use App\Models\AttendanceType;
use App\Models\Course;
use App\Models\Note;
use App\Models\Recitation;
use App\Models\ResultSetting;
use App\Models\Sabr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class CircleController extends Controller
{
    public function info()
    {
        try {
            $activeCourse = Course::where('is_active', true)->firstOrFail();
            $user = Auth::user();
            $teacher = $user->teacher;
            if (!$teacher) {
                return response()->json([
                    'message' => 'هذا الحساب غير مرتبط بسجل أستاذ.',
                ], 404);
            }

            $circle = $teacher->circle;
            $courseId = $activeCourse->id;
            $studentIds = $circle->students->pluck('id');

            //
            // 1) Attendance breakdown by type
            //
            $attRecords = Attendance::where('course_id', $courseId)
                ->whereIn('student_id', $studentIds)
                ->get();
            $total = $attRecords->count();
            $countsByType = $attRecords
                ->groupBy('type_id')
                ->map(fn($grp) => $grp->count());
            $attendanceStats = AttendanceType::all()
                ->mapWithKeys(fn($type) => [
                    $type->name => [
                        'count' => $countsByType->get($type->id, 0),
                        'ratio' => $total
                            ? round($countsByType->get($type->id, 0) / $total * 100, 2)
                            : 0.00,
                    ]
                ]);

            //
            // 2) Recitation metrics
            //
            $recs = Recitation::where('course_id', $courseId)
                ->whereIn('student_id', $studentIds)
                ->get();
            $recitationCount = $recs->count();
            $recitationAvg = $recitationCount
                ? round($recs->sum(fn($r) => assessmentRawScore($r)) / $recitationCount, 2)
                : 0.00;
            $recitationBySetting = $recs
                ->map(fn($r) => $r->calculateResult())
                ->countBy();

            //
            // 3) Sabr metrics
            //
            $sabs = Sabr::where('course_id', $courseId)
                ->whereIn('student_id', $studentIds)
                ->get();
            $sabrCount = $sabs->count();
            $sabrAvg = $sabrCount
                ? round($sabs->sum(fn($s) => assessmentRawScore($s)) / $sabrCount, 2)
                : 0.00;
            $sabrBySetting = $sabs
                ->map(fn($s) => $s->calculateResult())
                ->countBy();

            //
            // 4) Top-3 lists
            //
            // Preload related settings/values
            $recSettings = ResultSetting::where('type', 'recitation')->get();
            $sabrSettings = ResultSetting::where('type', 'sabr')->get();
            $attendanceValues = AttendanceType::pluck('value', 'id');

            $students = $circle->students; // a Collection<Student>

            $topOverall = $students
                ->map(fn($stu) => [
                    'id' => $stu->id,
                    'name' => $stu->user->name,
                    'points' => $stu->points,
                ])
                ->sortByDesc('points')
                ->values();

            $topReciters = $students
                ->map(fn($stu) => [
                    'id' => $stu->id,
                    'name' => $stu->user->name,
                    'points' => $stu->recitations()
                        ->where('course_id', $courseId)
                        ->get()
                        ->sum(fn($r) => optional(
                            $recSettings->first(
                                fn($s) =>
                                ($score = assessmentRawScore($r)) >= $s->min_res
                                && $score <= $s->max_res
                            )
                        )->points ?? 0)
                ])
                ->sortByDesc('points')
                ->values();

            $topSabrs = $students
                ->map(fn($stu) => [
                    'id' => $stu->id,
                    'name' => $stu->user->name,
                    'points' => $stu->sabrs()
                        ->where('course_id', $courseId)
                        ->get()
                        ->sum(fn($s) => optional(
                            $sabrSettings->first(
                                fn($set) =>
                                ($score = assessmentRawScore($s)) >= $set->min_res
                                && $score <= $set->max_res
                            )
                        )->points ?? 0)
                ])
                ->sortByDesc('points')
                ->values();

            $topAttendees = $students
                ->map(fn($stu) => [
                    'id' => $stu->id,
                    'name' => $stu->user->name,
                    'points' => $stu->attendances()
                        ->where('course_id', $courseId)
                        ->get()
                        ->sum(fn($att) => $attendanceValues->get($att->type_id, 0))
                ])
                ->sortByDesc('points')
                ->values();

            //
            // 5) Rankings among all circles by point-schemes
            //
            $allCircles = Circle::all();
            $attendanceRank = $this->rankByAttendances($allCircles, $circle->id, $courseId);
            $recitationRank = $this->rankByRecitations($allCircles, $circle->id, $courseId);
            $sabrRank = $this->rankBySabrs($allCircles, $circle->id, $courseId);


            $circleTotals = Circle::all()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'points' => $c->students->sum(fn($s) => $s->points),
                ])
                ->sortByDesc('points')
                ->values();

            // Find this circle’s rank (index + 1)
            $circleOverallRank = $circleTotals->search(fn($r) => $r['id'] === $circle->id) + 1;
            return response()->json([
                'circle' => $circle->name,
                'studentCount' => $circle->students->count(),
                'attendanceStats' => $attendanceStats,
                'recitationCount' => $recitationCount,
                'recitationAvg' => $recitationAvg,
                'recitationBySetting' => $recitationBySetting,
                'sabrCount' => $sabrCount,
                'sabrAvg' => $sabrAvg,
                'sabrBySetting' => $sabrBySetting,
                'circleOverallRank' => $circleOverallRank,
                'circlesCount' => Circle::count(),
                'attendanceRank' => $attendanceRank,
                'recitationRank' => $recitationRank,
                'sabrRank' => $sabrRank,
                'topOverall' => $topOverall,
                'topReciters' => $topReciters,
                'topSabrs' => $topSabrs,
                'topAttendees' => $topAttendees,

            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ غير متوقع. حاول مرة أخرى لاحقاً.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function basicInfo(Student $student)
    {
        try {
            // 1) Make sure the currently authenticated user is a teacher
            $user = Auth::user();
            $teacher = $user->teacher;
            if (!$teacher || is_null($student->getKey())) {
                return response()->json([
                    'message' => ' هذا الحساب غير مرتبط بسجل أستاذ.أو أن الطالب غير موجود'
                ], 403);
            }

            // 2) Ensure the student belongs to that teacher’s circle
            if ($student->circle_id !== $teacher->circle_id) {
                return response()->json([
                    'message' => 'حلقة الطالب غير متوافقة مع حلقة الأستاذ.'
                ], 403);
            }

            // 3) Safe: get active course (may be null)
            $course = Student::activeCourse();

            // 4) Gather counts
            $recitationsCount = $course
                ? $student->recitations()->where('course_id', $course->id)->count()
                : 0;
            $recitationsNames = $student->resultCounts('recitation');
            $sabrsCount = $course
                ? $student->sabrs()->where('course_id', $course->id)->count()
                : 0;
            $sabrsNames = $student->resultCounts('sabr');
            $positiveNotes = $student->notes()
                ->where('course_id', $course?->id)
                ->where('type', 'positive')
                ->count();

            $recitation_history = $student->recitationHistoryRows();
            $negativeNotes = $student->notes()
                ->where('course_id', $course?->id)
                ->where('type', 'negative')
                ->count();
            $level = $student->level->name;
            $attendances = $this->buildAttendanceList($student, $course);
            // 5) Build and return JSON
            return response()->json([
                'id' => $student->qr_token,
                'name' => $student->user->name,
                'level' => $level,
                'student_phone' => $student->student_phone,
                'father_phone' => $student->father_phone,
                'points' => $student->points,
                'rank_in_circle' => $student->rankInCircle(),
                'rank_in_mosque' => $student->rankInMosque(),
                'attendances' => $attendances,
                'recitation_history' => $recitation_history,
                'recitations_count' => $recitationsCount,
                'recitations_names' => $recitationsNames,
                'sabrs_count' => $sabrsCount,
                'sabrs_names' => $sabrsNames,
                'positive_notes' => $positiveNotes,
                'negative_notes' => $negativeNotes,
            ], 200);

        } catch (ModelNotFoundException $e) {
            // This shouldn’t really happen because of route‐model binding,
            // but in case someone bypasses that:
            return response()->json([
                'message' => 'طالب غير موجود.'
            ], 404);

        } catch (\Exception $e) {
            // Unexpected errors
            \Log::error('Error in basicInfo()', [
                'student_id' => $student->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'حدث خطأ أثناء جلب معلومات الطالب.'
            ], 500);
        }
    }
    public function students()
    {
        try {
            $activeCourse = Course::where('is_active', true)->first();
            $user = Auth::user();
            $teacher = $user->teacher;
            if (!$user || !$teacher) {
                return response()->json([
                    'message' => 'هذا الحساب غير مرتبط بسجل أستاذ.',
                ], 404);
            }
            $circle = $teacher->circle;
            $students = Student::with('user')
                ->where('circle_id', $circle->id)
                ->get()
                ->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->user->name,
                        'qr_token' => $student->qr_token
                    ];
                });
            return response()->json([
                'circle' => $circle->name,
                'students' => $students,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred. Please try again later.',
                'content' => $e->getMessage(),
            ], 500);
        }
    }

    private function rankByAttendances(Collection $circles, int $targetCircleId, int $courseId): int
    {
        $attendanceValues = AttendanceType::pluck('value', 'id')->all();

        $points = $circles->map(fn($c) => [
            'id' => $c->id,
            'points' => Attendance::where('course_id', $courseId)
                ->whereIn('student_id', $c->students->pluck('id'))
                ->get()
                ->sum(fn($att) => $attendanceValues[$att->type_id] ?? 0),
        ]);

        $sorted = $points->sortByDesc('points')->values();
        return $sorted->search(fn($r) => $r['id'] === $targetCircleId) + 1;
    }

    /**
     * Rank circles by total recitation points.
     */
    private function rankByRecitations(Collection $circles, int $targetCircleId, int $courseId): int
    {
        $settings = ResultSetting::where('type', 'recitation')->get();

        $points = $circles->map(fn($c) => [
            'id' => $c->id,
            'points' => Recitation::where('course_id', $courseId)
                ->whereIn('student_id', $c->students->pluck('id'))
                ->with(['mistakesRecords', 'level'])
                ->get()
                ->sum(function ($r) use ($settings) {
                    $raw = assessmentRawScore($r);
                    $setting = $settings
                        ->first(fn($s) => $raw >= $s->min_res && $raw <= $s->max_res);
                    return $setting->points ?? 0;
                }),
        ]);

        $sorted = $points->sortByDesc('points')->values();
        return $sorted->search(fn($r) => $r['id'] === $targetCircleId) + 1;
    }

    /**
     * Rank circles by total sabr points.
     */
    private function rankBySabrs(Collection $circles, int $targetCircleId, int $courseId): int
    {
        $settings = ResultSetting::where('type', 'sabr')->get();

        $points = $circles->map(fn($c) => [
            'id' => $c->id,
            'points' => Sabr::where('course_id', $courseId)
                ->whereIn('student_id', $c->students->pluck('id'))
                ->with(['mistakesRecords', 'level'])
                ->get()
                ->sum(function ($s) use ($settings) {
                    $raw = assessmentRawScore($s);
                    $setting = $settings
                        ->first(fn($r) => $raw >= $r->min_res && $raw <= $r->max_res);
                    return $setting->points ?? 0;
                }),
        ]);

        $sorted = $points->sortByDesc('points')->values();
        return $sorted->search(fn($r) => $r['id'] === $targetCircleId) + 1;
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
}
