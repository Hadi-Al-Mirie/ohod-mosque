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
use Exception;
use Illuminate\Support\Facades\Log;
class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 1) Validate incoming filters
            $data = $request->validate([
                'search_value' => 'nullable|string|min:1|max:255',
                'type' => 'nullable|integer|exists:attendance_types,id',
                'date' => 'nullable|date',
            ], [
                'search_value.min' => 'يجب أن لا يقل الاسم عن حرف واحد.',
                'search_value.max' => 'اسم الطالب طويل جداً (أقصى 255 حرف).',
                'type.exists' => 'نوع الحضور المحدد غير موجود.',
                'date.date' => 'التاريخ غير صالح.',
            ]);

            $cid = course_id();

            // 2) Build base query
            $query = Attendance::with('student.user', 'type')
                ->where('course_id', $cid);

            // 3) Apply filters
            if (!empty($data['search_value'])) {
                $query->whereHas('student.user', function ($q) use ($data) {
                    $q->where('name', 'LIKE', "%{$data['search_value']}%");
                });
            }

            if (!empty($data['type'])) {
                $query->where('type_id', $data['type']);
            }

            if (!empty($data['date'])) {
                // filter exact date; if you want range, adjust here
                $query->whereDate('attendance_date', $data['date']);
            }

            // 4) Paginate and return
            $attendances = $query
                ->orderBy('attendance_date', 'desc')
                ->paginate(10)
                ->withQueryString();

            return view('dashboard.attendances.index', compact('attendances'));
        } catch (ValidationException $ve) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (Exception $e) {
            \Log::error('Attendance index error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()
                ->back()
                ->with('danger', 'حدث خطأ غير متوقع: ' . $e->getMessage());
        }
    }



    public function create()
    {
        $circles = Circle::with('students.user')->get();
        $types = AttendanceType::select('id', 'name')->get();
        return view('dashboard.attendances.add', compact('circles', 'types'));
    }

    public function store(Request $request)
    {
        // Validate: require either circle_id or student_id
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
            $createdAt = $course->created_at->format('Y-m-d');
            $date = $request->input('attendance_date');

            if ($date < $createdAt) {
                throw ValidationException::withMessages([
                    'attendance_date' => ["تاريخ الحضور يجب أن يكون بعد أو في تاريخ بدء الدورة {$createdAt}"]
                ]);
            }

            $type = AttendanceType::findOrFail($request->input('type_id'));
            $justif = $type->name === 'غياب مبرر' ? $request->input('justification') : null;

            if ($type->name === 'غياب مبرر' && !$request->filled('justification')) {
                throw ValidationException::withMessages([
                    'justification' => ['الرجاء تقديم تبرير للغياب المبرر']
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
                // prevent duplicates
                if (
                    Attendance::where([
                        ['student_id', $stu->id],
                        ['attendance_date', $date],
                        ['type_id', $type->id],
                    ])->exists()
                ) {
                    continue;
                }
                $bRec = true;
                Attendance::create([
                    'student_id' => $stu->id,
                    'course_id' => $courseId,
                    'type_id' => $type->id,
                    'by_id' => $byId,
                    'justification' => $justif,
                    'attendance_date' => $date,
                ]);

                // update cached points
                $stu->update(['cashed_points' => $stu->points]);
            }
            if ($bRec == false) {
                return redirect()->route('admin.attendances.index')
                    ->with('danger', 'بيانات الحضور مسجلة مسبقا , قم بتعديلها');
            }
            return redirect()->route('admin.attendances.index')
                ->with('success', 'تم تسجيل الحضور بنجاح');

        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (Exception $e) {
            Log::error('Error registering attendance', ['msg' => $e->getMessage()]);
            return back()->withInput()->withErrors(['msg' => 'حدث خطأ أثناء تسجيل الحضور. حاول مرة أخرى']);
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
            return redirect()->back()
                ->withErrors($ve->errors())
                ->withInput()
                ->with('danger', 'يرجى تصحيح الأخطاء أدناه.');
        } catch (Exception $e) {
            Log::error('Error registering attendance', ['msg' => $e->getMessage()]);
            return back()->withInput()->withErrors(['msg' => 'حدث خطأ أثناء تسجيل الحضور. حاول مرة أخرى']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
