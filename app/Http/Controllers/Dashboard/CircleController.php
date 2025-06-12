<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\Circle;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Course;
use App\Models\Sabr;
use App\Models\Recitation;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Models\AttendanceType;
use App\Models\ResultSetting;
class CircleController extends Controller
{

    public function index(Request $request)
    {
        $request->validate([
            'search_value' => 'nullable|string|min:2|max:100',
        ]);
        $searchValue = $request->get('search_value');
        $currentCourse = Course::latest()->first();
        if (is_null($currentCourse)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('danger', 'أنشئ دورة ثم أعد المحاولة لاحقاً.');
        }
        $circlesQuery = Circle::withCount('students')
            ->with(['teacher.user:id,name'])
            ->withSum('students as points', 'cashed_points');
        if ($searchValue) {
            $circlesQuery->where('name', 'like', "%{$searchValue}%");
        }
        $circlesQuery->orderBy('points', 'desc');
        $circles = $circlesQuery->paginate(10);
        return view('dashboard.circles.index', compact('circles'))
            ->with('order_by');
    }
    public function create()
    {
        $teachers = User::where('role_id', 2)
            ->whereHas('teacher', function ($query) {
                $query->whereNull('circle_id');
            })
            ->select('id', 'name')
            ->get();
        return view('dashboard.circles.add', compact('teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'circle_name' => 'required|string|max:255',
                'teacher_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('users', 'id')->where('role_id', 2),
                ],
            ]);

            DB::transaction(function () use ($data) {
                $circle = Circle::create([
                    'name' => $data['circle_name'],
                ]);

                if (!empty($data['teacher_id'])) {
                    $teacher = Teacher::where('user_id', $data['teacher_id'])->first();
                    if ($teacher) {
                        $teacher->update(['circle_id' => $circle->id]);
                    }
                }
            });

            return redirect()->route('admin.circles.index')
                ->with('success', 'تم إنشاء الحلقة بنجاح');
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (Exception $e) {
            Log::error('Error creating circle', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('danger', 'حدث خطأ غير متوقع أثناء إنشاء الحلقة. حاول مرةً أخرى.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Circle $circle)
    {
        $activeCourse = Course::where('is_active', true)->first();
        $studentIds = $circle->students->pluck('id');

        $records = Attendance::where('course_id', $activeCourse->id)
            ->whereIn('student_id', $studentIds)
            ->get();

        $totalAttendances = $records->count();

        $countsByTypeId = $records
            ->groupBy('type_id')
            ->map(fn($group) => $group->count());

        $attendanceStats = AttendanceType::all()
            ->mapWithKeys(function (AttendanceType $type) use ($countsByTypeId, $totalAttendances) {
                $count = $countsByTypeId->get($type->id, 0);
                $ratio = $totalAttendances > 0
                    ? round(($count / $totalAttendances) * 100, 2)
                    : 0.00;
                return [
                    $type->name => [
                        'count' => $count,
                        'ratio' => $ratio,
                    ]
                ];
            })
            ->toArray();

        $recs = Recitation::where('course_id', $activeCourse->id)
            ->whereIn('student_id', $studentIds)
            ->get();
        $recitationCount = $recs->count();

        $recitationAvg = $recitationCount > 0
            ? $recs->sum(fn($r) => (int) assessmentRawScore($r)) / $recitationCount
            : 0;

        $recitationBySetting = $recs
            ->map(fn($r) => $r->calculateResult())
            ->countBy()
            ->toArray();

        $sabs = Sabr::where('course_id', $activeCourse->id)
            ->whereIn('student_id', $studentIds)
            ->get();
        $sabrCount = $sabs->count();
        $sabrAvg = $sabrCount > 0
            ? $sabs->sum(fn($s) => (int) assessmentRawScore($s)) / $sabrCount
            : 0;

        $sabrBySetting = $sabs
            ->map(fn($s) => $s->calculateResult())
            ->countBy()
            ->toArray();


        $topOverall = $circle->students
            ->map(fn($stu) => [
                'student' => $stu,
                'points' => $stu->points,
            ])
            ->sortByDesc('points')
            ->take(3)
            ->values();

        $topReciters = $circle->students
            ->map(fn($stu) => [
                'student' => $stu,
                'points' => $stu->recitations()
                    ->where('course_id', $activeCourse->id)
                    ->get()
                    ->sum(fn($r) => optional(
                        ResultSetting::where('type', 'recitation')
                            ->where('min_res', '<=', assessmentRawScore($r))
                            ->where('max_res', '>=', assessmentRawScore($r))
                            ->first()
                    )->points ?? 0)
            ])
            ->sortByDesc('points')
            ->take(3)
            ->values();

        $topSabrs = $circle->students
            ->map(fn($stu) => [
                'student' => $stu,
                'points' => $stu->sabrs()
                    ->where('course_id', $activeCourse->id)
                    ->get()
                    ->sum(fn($s) => optional(
                        ResultSetting::where('type', 'sabr')
                            ->where('min_res', '<=', assessmentRawScore($s))
                            ->where('max_res', '>=', assessmentRawScore($s))
                            ->first()
                    )->points ?? 0)
            ])
            ->sortByDesc('points')
            ->take(3)
            ->values();

        $attendanceValues = AttendanceType::pluck('value', 'id');
        $topAttendees = $circle->students
            ->map(fn($stu) => [
                'student' => $stu,
                'points' => $stu->attendances()
                    ->where('course_id', $activeCourse->id)
                    ->get()
                    ->sum(fn($att) => $attendanceValues->get($att->type_id, 0))
            ])
            ->sortByDesc('points')
            ->take(3)
            ->values();

        $allCircles = Circle::all();
        $attendanceRank = $this->rankByAttendances($allCircles, $circle->id, $activeCourse->id);
        $recitationRank = $this->rankByRecitations($allCircles, $circle->id, $activeCourse->id);
        $sabrRank = $this->rankBySabrs($allCircles, $circle->id, $activeCourse->id);

        return view('dashboard.circles.show', [
            'circle' => $circle,
            'attendanceStats' => $attendanceStats,
            'recitationCount' => $recitationCount,
            'recitationAvg' => $recitationAvg,
            'recitationBySetting' => $recitationBySetting,
            'sabrCount' => $sabrCount,
            'sabrAvg' => $sabrAvg,
            'sabrBySetting' => $sabrBySetting,
            'attendanceRank' => $attendanceRank,
            'recitationRank' => $recitationRank,
            'sabrRank' => $sabrRank,
            'topReciters' => $topReciters,
            'topSabrs' => $topSabrs,
            'topAttendees' => $topAttendees,
            'topOverall' => $topOverall,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Circle $circle)
    {
        // Retrieve all teachers to populate the dropdown
        $teachers = Teacher::with('user')->get();
        return view('dashboard.circles.edit', compact('circle', 'teachers'));
    }
    public function update(Request $request, Circle $circle)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'teacher_id' => 'nullable|exists:teachers,id',
            ], [
                'name.required' => 'اسم الحلقة مطلوب.',
            ]);

            DB::transaction(function () use ($circle, $data) {
                $circle->update(['name' => $data['name']]);

                // unassign old
                Teacher::where('circle_id', $circle->id)
                    ->update(['circle_id' => null]);

                if (!empty($data['teacher_id'])) {
                    Teacher::find($data['teacher_id'])
                        ->update(['circle_id' => $circle->id]);
                }
            });

            return redirect()->route('admin.circles.index')
                ->with('success', 'تم تحديث بيانات الحلقة بنجاح');
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (Exception $e) {
            Log::error('Error updating circle', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('danger', 'حدث خطأ غير متوقع أثناء تحديث الحلقة.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Circle $circle)
    {
        //
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
}