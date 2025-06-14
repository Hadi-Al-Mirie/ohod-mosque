<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\Circle;
use App\Models\Student;
use App\Models\LevelMistake;
use App\Models\ResultSetting;
use App\Models\Course;
use App\Models\User;
use App\Models\Sabr;
use App\Models\Recitation;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Level;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // 1) Validate
            $request->validate([
                'search_value' => 'nullable|string|min:2|max:100',
                'order_by' => 'string|in:points,attendance,sabrs,recitations',
                'circle_id' => 'nullable|exists:circles,id',
            ]);

            $searchValue = $request->get('search_value');
            $orderBy = $request->get('order_by', 'points');
            $circleId = $request->get('circle_id');

            // 2) Active course
            $active = Course::where('is_active', true)->firstOrFail();
            $cid = $active->id;

            //
            // 3a) raw‐score per recitation
            //
            // 3a) raw‐score per recitation
            $rawRecSub = DB::table('recitations')
                ->where('course_id', $cid)
                ->leftJoin('mistakes_recordes as mr', 'mr.recitation_id', 'recitations.id')
                ->leftJoin('level_mistakes as lm', function ($j) {
                    $j->on('lm.mistake_id', 'mr.mistake_id')
                        ->on('lm.level_id', 'recitations.level_id');
                })
                ->select([
                    'recitations.id',
                    'recitations.student_id',
                    DB::raw('100 - COALESCE(SUM(lm.value), 0) AS raw_score'),
                ])
                ->groupBy('recitations.id', 'recitations.student_id');

            // 3b) bucket & sum recitation points
            $recPointsSub = DB::query()
                ->fromSub($rawRecSub, 'raw_rec')
                ->join('result_settings as rs', function ($j) {
                    $j->on('rs.type', DB::raw("'recitation'"))
                        ->whereColumn('rs.min_res', '<=', 'raw_rec.raw_score')
                        ->whereColumn('rs.max_res', '>=', 'raw_rec.raw_score');
                })
                ->select([
                    'raw_rec.student_id',
                    DB::raw('SUM(rs.points) AS rec_points'),
                ])
                ->groupBy('raw_rec.student_id');

            // 4a) raw‐score per sabr
            $rawSabrSub = DB::table('sabrs')
                ->where('course_id', $cid)
                ->leftJoin('mistakes_recordes as mr', 'mr.sabr_id', 'sabrs.id')
                ->leftJoin('level_mistakes as lm', function ($j) {
                    $j->on('lm.mistake_id', 'mr.mistake_id')
                        ->on('lm.level_id', 'sabrs.level_id');
                })
                ->select([
                    'sabrs.id',
                    'sabrs.student_id',
                    DB::raw('100 - COALESCE(SUM(lm.value), 0) AS raw_score'),
                ])
                ->groupBy('sabrs.id', 'sabrs.student_id');

            // 4b) bucket & sum sabr points
            $sabrPointsSub = DB::query()
                ->fromSub($rawSabrSub, 'raw_sabr')
                ->join('result_settings as rs', function ($j) {
                    $j->on('rs.type', DB::raw("'sabr'"))
                        ->whereColumn('rs.min_res', '<=', 'raw_sabr.raw_score')
                        ->whereColumn('rs.max_res', '>=', 'raw_sabr.raw_score');
                })
                ->select([
                    'raw_sabr.student_id',
                    DB::raw('SUM(rs.points) AS sabr_points'),
                ])
                ->groupBy('raw_sabr.student_id');


            //
            // 5) main student query
            //
            $students = Student::query()
                ->select('students.*')

                // total recitation points
                ->selectSub(
                    fn($q) => $q
                        ->fromSub($recPointsSub, 'rps')
                        ->whereColumn('rps.student_id', 'students.id')
                        ->selectRaw('COALESCE(rps.rec_points,0)')
                    ,
                    'recitations_points'
                )

                // total sabr points
                ->selectSub(
                    fn($q) => $q
                        ->fromSub($sabrPointsSub, 'sps')
                        ->whereColumn('sps.student_id', 'students.id')
                        ->selectRaw('COALESCE(sps.sabr_points,0)')
                    ,
                    'sabrs_points'
                )

                // total attendance points
                ->selectSub(function ($q) use ($cid) {
                    $q->from('attendances as a')
                        ->where('a.course_id', $cid)
                        ->whereColumn('a.student_id', 'students.id')
                        ->join('attendance_types as at', 'at.id', 'a.type_id')
                        ->selectRaw('COALESCE(SUM(CASE
                      WHEN a.type_id=1 THEN at.value
                      WHEN a.type_id=4 THEN 0
                      ELSE -2
                    END),0)');
                }, 'attendance_points')

                ->with(['user:id,name', 'circle:id,name'])

                ->when(
                    $searchValue,
                    fn($q) =>
                    $q->whereHas(
                        'user',
                        fn($q2) =>
                        $q2->where('name', 'like', "%{$searchValue}%")
                    )
                )

                // 6b) optional circle filter
                ->when(
                    $circleId,
                    fn($q) =>
                    $q->where('circle_id', $circleId)
                );

            match ($orderBy) {
                'attendance' => $students->orderByDesc('attendance_points'),
                'sabrs' => $students->orderByDesc('sabrs_points'),
                'recitations' => $students->orderByDesc('recitations_points'),
                default => $students->orderByDesc('cashed_points'),
            };

            // 8) paginate + preserve
            $students = $students
                ->paginate(10)
                ->withQueryString();
            $circles = Circle::orderBy('name')->pluck('name', 'id');
            return view('dashboard.students.index', compact('students', 'orderBy', 'circles'));

        } catch (ValidationException $ve) {
            \Log::error('StudentController@index error', ['e' => $ve->getMessage()]);
            return back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة بيانات البحث.');
        } catch (Exception $e) {
            \Log::error('StudentController@index error', ['e' => $e->getMessage()]);
            return back()->with('danger', 'حدث خطأ أثناء جلب بيانات الطلاب.');
        }
    }







    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $circles = Circle::select('id', 'name')->get();
        $levels = Level::select('id', 'name')->get();
        return view('dashboard.students.add', compact('circles', 'levels'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'circle' => 'required|exists:circles,id',
                'level' => 'required|exists:levels,id',
                'password' => 'required|string|min:8|max:64',
                'birthday' => 'nullable|date',
                'father_name' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'father_work' => 'nullable|string|max:255',
                'mother_work' => 'nullable|string|max:255',
                'student_phone' => 'nullable|string|max:255',
                'father_phone' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'school' => 'nullable|string|max:255',
                'class' => 'nullable|string|max:255',
            ]);
            $qrcode = null;
            $student = null;
            $exists = Student::whereHas(
                'user',
                fn($q) =>
                $q->where('name', $request->input('name'))
            )->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'name' => ["هذا الطالب موجود مسبقا"]
                ]);
            }
            DB::transaction(function () use ($request, &$qrcode, &$student) {
                $user = User::create([
                    'name' => $request->input('name'),
                    'password' => $request->input('password'),
                    'role_id' => 4,
                ]);
                do {
                    $token = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                } while (Student::where('qr_token', $token)->exists());
                $student = Student::create([
                    'student_phone' => $request->input('student_phone'),
                    'father_phone' => $request->input('father_phone'),
                    'location' => $request->input('location'),
                    'birth' => $request->input('birthday'),
                    'class' => $request->input('class'),
                    'school' => $request->input('school'),
                    'father_name' => $request->input('father_name'),
                    'father_job' => $request->input('father_work'),
                    'mother_name' => $request->input('mother_name'),
                    'mother_job' => $request->input('mother_work'),
                    'user_id' => $user->id,
                    'circle_id' => $request->input('circle'),
                    'level_id' => $request->input('level'),
                    'qr_token' => $token,
                ]);
                $qrcode = QrCode::format('png')->size(500)->generate($token);
            });
            return redirect()->route('admin.students.show', ['student' => $student->id]);
        } catch (ValidationException $ve) {
            return back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة بيانات الطالب.');
        } catch (Exception $e) {
            Log::error('Error registering attendance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            Log::error('Error listing sabrs', ['error' => $e->getMessage()]);
            return back()
                ->with('danger', 'حدث خطأ أثناء تسجبل الطالب.');
        }
    }


    public function show(Student $student)
    {
        try {
            $course = Student::activeCourse();
            $now = now();
            $oneWeekAgo = (clone $now)->subWeek();    // today – 1 week
            $twoWeeksAgo = (clone $now)->subWeeks(2);  // today – 2 weeks
            $pointsUpToTwo = $student->pointsUpTo($twoWeeksAgo);
            $pointsUpToOne = $student->pointsUpTo($oneWeekAgo);
            $pointsGainedLastWeek = $pointsUpToOne - $pointsUpToTwo;
            return view('dashboard.students.show', [
                'qrcode' => QrCode::format('png')->size(500)->generate($student->qr_token),
                'student' => $student,
                'numberOfWorkingDays' => $student->workingDaysCount(),

                // recitation & sabr
                'recitationsCount' => $student->recitations()->where('course_id', $course->id)->count(),
                'recitationAvg' => round($student->rawScores('recitation')->avg(), 2),
                'recitationResultCounts' => $student->resultCounts('recitation'),
                'recitationsPoints' => $student->recitationPoints(),

                'sabrCount' => $student->sabrs()->where('course_id', $course->id)->count(),
                'sabrAvg' => round($student->rawScores('sabr')->avg(), 2),
                'sabrResultCounts' => $student->resultCounts('sabr'),
                'sabrPoints' => $student->sabrPoints(),

                // attendance & notes
                'attendanceStats' => $student->attendanceStats(),
                'notesStats' => $student->notesStats(),

                // rankings
                'rankInCircle' => $student->rankInCircle(),
                'rankInMosque' => $student->rankInMosque(),
                'rankInCirclePrev' => $student->rankInCircle($oneWeekAgo),
                'rankInMosquePrev' => $student->rankInMosque($oneWeekAgo),

                // weekly deltas
                'pointsGainedThisWeek' => $student->pointsDelta($oneWeekAgo),
                'pointsGainedLastWeek' => $student->pointsDelta($twoWeeksAgo, $oneWeekAgo),
                'improvementPercent' => percent_change(
                    $student->pointsDelta($oneWeekAgo),
                    $student->pointsDelta($twoWeeksAgo, $oneWeekAgo)
                ),

                // for counts in the view
                'circleStudentsCount' => optional($student->circle)->students->count() ?: 0,
                'mosqueStudentsCount' => Student::count(),
            ]);
        } catch (Exception $e) {
            Log::error('Error showing student', ['error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'حدث خطأ أثناء جلب بيانات الطالب.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        $circles = Circle::select('id', 'name')->get();
        $levels = Level::select('id', 'name')->get();
        return view('dashboard.students.edit', compact('student', 'circles', 'levels'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:2|max:255',
                'circle' => 'required|exists:circles,id',
                'level' => 'required|exists:levels,id',
                'birthday' => 'nullable|date',
                'father_name' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'father_work' => 'nullable|string|max:255',
                'mother_work' => 'nullable|string|max:255',
                'student_phone' => 'nullable|string|max:255',
                'father_phone' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'school' => 'nullable|string|max:255',
                'class' => 'nullable|string|max:255',
            ]);
            DB::transaction(function () use ($request, $student) {
                $student->user->update([
                    'name' => $request->input('name'),
                ]);
                $student->update([
                    'student_phone' => $request->input('student_phone'),
                    'father_phone' => $request->input('father_phone'),
                    'location' => $request->input('location'),
                    'birth' => $request->input('birthday'),
                    'class' => $request->input('class'),
                    'school' => $request->input('school'),
                    'father_name' => $request->input('father_name'),
                    'father_job' => $request->input('father_work'),
                    'mother_name' => $request->input('mother_name'),
                    'mother_job' => $request->input('mother_work'),
                    'circle_id' => $request->input('circle'),
                    'level_id' => $request->input('level'),
                ]);
            });
            return redirect()->route('admin.students.show', $student->id)
                ->with('success', 'تم تحديث بيانات الطالب بنجاح.');
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (Exception $e) {
            Log::error('Error edit student', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء تحديث بيانات الطالب.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }
}