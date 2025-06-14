<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\AttendanceType;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Circle;
use App\Models\Course;
use Carbon\Carbon;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 1) Validate incoming filters
            $data = $request->validate([
                'student_name' => 'nullable|string|min:1|max:255',
                'type' => ['nullable', 'integer', 'exists:attendance_types,id'],
                'date' => 'nullable|date',
            ], [
                'search_value.min' => 'يجب أن لا يقل الاسم عن حرف واحد.',
                'search_value.max' => 'اسم الطالب طويل جداً (أقصى 255 حرف).',
                'teacher_name.max' => 'اسم الأستاذ طويل جداً (أقصى 255 حرف).',
                'type.exists' => 'نوع الحضور المحدد غير موجود.',
                'date.date' => 'التاريخ غير صالح.',
            ]);

            $cid = course_id();

            // 2) Build base query
            $query = Attendance::with(['student.user', 'type', 'creator'])
                ->where('course_id', $cid);

            // 3a) Filter by student name
            if (!empty($data['student_name'])) {
                $query->whereHas(
                    'student.user',
                    fn($q) =>
                    $q->where('name', 'like', "%{$data['student_name']}%")
                );
            }

            // 3c) Filter by attendance type
            if (!empty($data['type'])) {
                $query->where('type_id', $data['type']);
            }

            // 3d) Filter by exact date
            if (!empty($data['date'])) {
                $query->whereDate('attendance_date', $data['date']);
            }
            // 4) Paginate & return
            $attendances = $query
                ->orderBy('attendance_date', 'desc')
                ->paginate(10)
                ->withQueryString();

            $types = AttendanceType::all();

            return view('dashboard.attendances.index', compact('attendances', 'types'));
        } catch (ValidationException $ve) {
            $types = AttendanceType::all();
            return view('dashboard.attendances.index', [
                'attendances' => collect(), // Empty collection
                'types' => $types,
            ])->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (Exception $e) {
            \Log::error('Attendance index error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('danger', 'حدث خطأ غير متوقع: ');
        }
    }



    public function create()
    {
        $circles = Circle::with('students.user')->get();
        $students = Student::with('user')->get();
        $types = AttendanceType::select('id', 'name')->get();
        return view('dashboard.attendances.add', compact('circles', 'students', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'circle_id' => 'nullable|required_without:student_id|exists:circles,id',
            'student_id' => 'nullable|required_without:circle_id|exists:students,id',
            'type_id' => 'required|exists:attendance_types,id',
            'justification' => 'nullable|string',
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'circle_id.required_without' => 'اختر حلقة أو طالباً واحداً.',
            'student_id.required_without' => 'اختر حلقة أو طالباً واحداً.',
        ]);

        try {
            $course = Course::where('is_active', true)->firstOrFail();

            // 3) Extract its start + end dates
            $start = Carbon::parse($course->start_date)->format('Y-m-d');
            $end = Carbon::parse($course->end_date)->format('Y-m-d');
            $date = $request->input('attendance_date');

            // 4) Enforce that attendance_date ∈ [start_date, end_date]
            if ($date < $start || $date > $end) {
                throw ValidationException::withMessages([
                    'attendance_date' => [
                        "تاريخ الحضور يجب أن يكون بين {$start} و {$end}."
                    ]
                ]);
            }

            // 5) Justification rules
            $type = AttendanceType::findOrFail($request->input('type_id'));
            $justif = $type->name === 'غياب مبرر'
                ? $request->input('justification')
                : null;

            if ($type->name === 'غياب مبرر' && !$request->filled('justification')) {
                throw ValidationException::withMessages([
                    'justification' => ['الرجاء تقديم تبرير للغياب المُبرر.']
                ]);
            }


            // Determine targets
            if ($studentId = $request->input('student_id')) {
                $targets = Student::where('id', $studentId)->get();
            } else {
                $targets = Circle::findOrFail($request->input('circle_id'))->students;
            }

            $byId = Auth::id();
            $courseId = $course->id;
            $bRec = false;
            foreach ($targets as $stu) {
                $attributes = [
                    'student_id' => $stu->id,
                    'course_id' => $course->id,
                    'attendance_date' => $date,
                ];
                $justif = $type->name === 'غياب مبرر'
                    ? $request->input('justification')
                    : null;
                Attendance::updateOrCreate(
                    $attributes,
                    [
                        'type_id' => $type->id,
                        'by_id' => $byId,
                        'justification' => $justif,
                    ]
                );
                $stu->update(['cashed_points' => $stu->points]);
            }
            return redirect()->route('admin.attendances.index')
                ->with('success', 'تم تسجيل الحضور بنجاح');

        } catch (ValidationException $ve) {
            return back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (ModelNotFoundException $me) {
            return back()
                ->withInput()
                ->with('danger', 'فشل في ايجاد الحلقة او الطالب');
        } catch (Exception $e) {
            Log::error('Error registering attendance', ['msg' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('danger', 'حدث خطأ أثناء تسجيل الحضور.');
        }
    }


    public function show(Attendance $attendance)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        $types = AttendanceType::select('id', 'name')->get();
        return view('dashboard.attendances.edit', compact('attendance', 'types'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        try {
            $request->validate([
                'type_id' => 'required|exists:attendance_types,id',
                'justification' => 'nullable|string'
            ]);
            $attendanceType = AttendanceType::find($request->input('type_id'));
            if (
                $attendanceType->name === 'غياب مبرر'
                && !$request->filled('justification')
            ) {
                throw ValidationException::withMessages([
                    'justification' => ['الرجاء تقديم تبرير للغياب المبرر'],
                ]);
            }
            DB::transaction(function () use ($request, $attendance, $attendanceType) {
                $attendance->update([
                    'type_id' => $request->input('type_id'),
                    'justification' => ($attendanceType && $attendanceType->name === 'غياب مبرر')
                        ? $request->input('justification')
                        : null,
                ]);
            });
            $student = $attendance->student;
            $student->update(['cashed_points' => $student->points]);
            return redirect()->route('admin.attendances.index')
                ->with('success', 'تم تحديث بيانات الحضور بنجاح');
        } catch (ValidationException $ve) {
            return back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (Exception $e) {
            Log::error('Error registering attendance', ['msg' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('danger', 'حدث خطأ أثناء تحديث الحضور .');
        }
    }
    public function registerDaily(Request $request)
    {
        // 1) Find the active course
        $course = Course::where('is_active', true)->first();
        if (!$course) {
            return redirect()->back()
                ->with('info', 'لا توجد دورة نشطة حالياً—تم التخطي.');
        }

        // 2) Is today a working day?
        $workingDays = json_decode($course->working_days, true) ?: [];
        $today = Carbon::today();
        $weekday = $today->dayOfWeek;          // 0 = Sunday, 1 = Monday, … 6 = Saturday
        $dateString = $today->format('Y-m-d');

        if (!in_array($weekday, $workingDays, true)) {
            return redirect()->back()
                ->with('info', "اليوم ({$dateString}) ليس يوم عمل للدورة—تم التخطي.");
        }

        // 3) Find an admin to stamp “by_id”
        $admin = User::where('role_id', 1)->first();
        if (!$admin) {
            return redirect()->back()
                ->with('danger', 'لم يتم العثور على مستخدم أدمن (role_id=1).');
        }

        // 4) Loop all students and insert missing attendance rows
        $typeId = 2;   // daily attendance type
        $students = Student::all();
        $created = 0;

        foreach ($students as $student) {
            $already = Attendance::where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->whereDate('attendance_date', $dateString)
                ->exists();

            if (!$already) {
                Attendance::create([
                    'student_id' => $student->id,
                    'by_id' => $admin->id,
                    'course_id' => $course->id,
                    'type_id' => $typeId,
                    'justification' => null,
                    'attendance_date' => $dateString,
                ]);
                $created++;
            }
        }

        // 5) Redirect back with a flash message
        return redirect()->back()
            ->with('success', "تم تسجيل الحضور لـ {$created} طالب/طالبة في {$dateString}.");
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
