<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Circle;
use App\Models\Course;
use App\Models\Attendance;
use App\Models\Recitation;
use App\Models\Sabr;
use App\Models\Note;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AttendanceType;
use Illuminate\Validation\ValidationException;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'search_value' => 'nullable|string|max:255|min:1'
        ]);
        $query = Course::query()->where('is_active', false);
        if ($request->has('search_value')) {
            $query->where('name', 'like', '%' . $request->search_value . '%');
        }
        $courses = $query->paginate(10);
        return view('dashboard.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.courses.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // 1) Validate inputs
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'working_days' => 'required|array',
                'working_days.*' => 'integer|in:0,1,2,3,4,5,6',
            ], [
                'name.required' => 'اسم الدورة مطلوب.',
                'start_date.required' => 'تاريخ البدء مطلوب.',
                'end_date.required' => 'تاريخ الانتهاء مطلوب.',
                'end_date.after' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البدء.',
                'working_days.required' => 'أيام العمل مطلوبة.',
                'working_days.*.integer' => 'أيام العمل يجب أن تكون أرقاماً صحيحة.',
                'working_days.*.in' => 'أيام العمل يجب أن تكون بين 0 و 6.',
            ]);
            $active = Course::where('is_active', true)->first();
            if ($active) {
                $activeEnd = Carbon::parse($active->end_date)->endOfDay();
                $newStart = Carbon::parse($data['start_date'])->startOfDay();
                Log::error('Error creating course', ['error' => $activeEnd]);
                Log::error('Error creating course', ['error' => $newStart]);
                if ($newStart->lte($activeEnd)) {
                    throw ValidationException::withMessages([
                        'start_date' => ['تاريخ البدء يتداخل مع دورة حالية. يجب أن يكون بعد ' . $activeEnd->toDateString() . '.'],
                    ]);
                }
            }
            DB::transaction(function () use ($data) {
                Course::create([
                    'name' => $data['name'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'working_days' => json_encode($data['working_days']),
                    'is_active' => false,
                ]);
            });
            return redirect()
                ->route('admin.courses.index')
                ->with('success', 'تم إنشاء الدورة بنجاح.');
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'هناك أخطاء في البيانات المدخلة. يرجى التصحيح والمحاولة مجددًا.');
        } catch (Exception $e) {
            Log::error('Error creating course', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->withInput()
                ->with('danger', 'حدث خطأ غير متوقع أثناء إنشاء الدورة. حاول مرةً أخرى لاحقًا.');
        }
    }

    public function show(Course $course)
    {
        $courseId = $course->id;
        $circles = Circle::with('students')->get();
        $attendanceValues = AttendanceType::pluck('value', 'id')->all();
        $categories = $colors = $chartData = $tableRows = [];

        foreach ($circles as $circle) {
            $studentIds = $circle->students->pluck('id');
            $studentCount = $studentIds->count();

            // === Sabr ===
            $sabs = Sabr::where('course_id', $courseId)
                ->whereIn('student_id', $studentIds)
                ->get();
            $sabrCount = $sabs->count();
            $sabrAvg = $sabrCount
                ? round($sabs->sum(fn($s) => assessmentRawScore($s)) / $sabrCount, 2)
                : 0;

            // === Recitation ===
            $recs = Recitation::where('course_id', $courseId)
                ->whereIn('student_id', $studentIds)
                ->get();
            $recCount = $recs->count();
            $recAvg = $recCount
                ? round($recs->sum(fn($r) => assessmentRawScore($r)) / $recCount, 2)
                : 0;

            $attRecords = Attendance::where('course_id', $courseId)
                ->whereIn('student_id', $studentIds)
                ->get();

            $attPoints = $attRecords->sum(
                fn($att) =>
                ($attendanceValues[$att->type_id] ?? 0)
            );

            // === Notes ===
            $notesQ = Note::where('course_id', $courseId)
                ->whereIn('student_id', $studentIds)
                ->where('status', 'approved');
            $notesCount = $notesQ->count();
            $netNotePts = $notesQ->sum('value');

            // === Aggregate student points ===
            $totalPoints = $circle->students
                ->sum(fn($s) => $s->calculatePoints($courseId));
            $avgPoints = $studentCount
                ? round($totalPoints / $studentCount, 2)
                : 0;

            // === Overall performance score ===
            $performance = round(
                ($avgPoints),
                2
            );

            // Collect for chart & table
            $categories[] = $circle->name;
            $colors[] = '#' . substr(md5($circle->id), 0, 6);
            $chartData[] = $performance;

            $tableRows[] = [
                'circle' => $circle->name,
                'students' => $studentCount,
                'sabr_count' => $sabrCount,
                'sabr_avg' => $sabrAvg,
                'rec_count' => $recCount,
                'rec_avg' => $recAvg,
                'att_points' => $attPoints,
                'att_total' => $attRecords->count(),
                // 'att_rate' => $attRecords->count()
                //     ? round($attPoints / ($attRecords->count() * max($attendanceValues)) * 100, 2)
                //     : 0,
                'notes_count' => $notesCount,
                'net_notes_points' => $netNotePts,
                'avg_points' => $avgPoints,
                'perf_score' => $performance,
            ];

        }
        usort($tableRows, fn($a, $b) => $b['att_points'] <=> $a['att_points']);
        return view('dashboard.courses.show', compact(
            'course',
            'categories',
            'colors',
            'chartData',
            'tableRows'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        return view('dashboard.courses.edit', compact('course'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'working_days' => 'required|array|min:1',
                'working_days.*' => 'integer|in:0,1,2,3,4,5,6',
            ], [
                'name.required' => 'اسم الدورة مطلوب.',
                'name.string' => 'اسم الدورة غير صالح.',
                'name.max' => 'اسم الدورة طويل جداً.',
                'start_date.required' => 'تاريخ البدء مطلوب.',
                'start_date.date' => 'تاريخ البدء غير صالح.',
                'end_date.required' => 'تاريخ الانتهاء مطلوب.',
                'end_date.date' => 'تاريخ الانتهاء غير صالح.',
                'end_date.after' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البدء.',
                'working_days.required' => 'حدد أيام العمل على الأقل.',
                'working_days.array' => 'أيام العمل غير صالحة.',
                'working_days.*.integer' => 'أيام العمل يجب أن تكون أرقاماً.',
                'working_days.*.in' => 'أيام العمل يجب أن تكون بين 0 و 6.',
            ]);
            $active = Course::where('is_active', true)->first();
            if (!$active || $active->id !== $course->id) {
                throw ValidationException::withMessages([
                    'name' => ['لا يمكن تعديل دورة غير نشطة.']
                ]);
            }
            DB::transaction(function () use ($course, $data) {
                $course->update([
                    'name' => $data['name'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'working_days' => json_encode($data['working_days']),
                ]);
            });
            return redirect()
                ->route('admin.courses.index')
                ->with('success', 'تم تعديل الدورة بنجاح.');

        } catch (ValidationException $ve) {
            return back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'هناك أخطاء في البيانات المدخلة.');
        } catch (Exception $e) {
            Log::error('Error updating course', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withInput()
                ->with('danger', 'حدث خطأ غير متوقع أثناء تعديل الدورة. حاول مرةً أخرى لاحقًا.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        //
    }

}
