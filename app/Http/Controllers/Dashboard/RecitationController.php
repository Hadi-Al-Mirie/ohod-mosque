<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Recitation;
use App\Models\ResultSetting;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Validation\Rule;
class RecitationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'search_field' => 'nullable|string|in:student,teacher,result',
                'search_value' => 'nullable|string|min:1|max:255',
                'student_name' => 'nullable|string|max:255',
                'teacher_name' => 'nullable|string|max:255',
                'result' => ['nullable', 'string', Rule::in(ResultSetting::where('type', 'recitation')->pluck('name'))],
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ], [
                'date_to.after_or_equal' => 'تاريخ "إلى" يجب أن يكون بعد أو يساوي "من".',
            ]);

            $cid = course_id();
            $settings = ResultSetting::where('type', 'recitation')->orderBy('min_res')->get();

            // build raw_score subquery (unchanged)
            $scoreSub = DB::table('recitations')
                ->select('recitations.id', DB::raw('100 - COALESCE(SUM(lm.value), 0) AS raw_score'))
                ->leftJoin('mistakes_recordes AS mr', 'mr.recitation_id', 'recitations.id')
                ->leftJoin('level_mistakes AS lm', function ($j) {
                    $j->on('lm.mistake_id', 'mr.mistake_id')
                        ->on('lm.level_id', 'recitations.level_id');
                })
                ->where('recitations.course_id', $cid)
                ->groupBy('recitations.id');

            $base = Recitation::query()
                ->select('recitations.*')
                ->where('recitations.course_id', $cid)
                ->joinSub($scoreSub, 'scores', 'scores.id', 'recitations.id')
                ->leftJoin('result_settings AS rs', function ($j) {
                    $j->on('rs.type', DB::raw("'recitation'"))
                        ->on('rs.min_res', '<=', 'scores.raw_score')
                        ->on('rs.max_res', '>=', 'scores.raw_score');
                })
                ->addSelect('rs.name AS result_name')
                ->with(['student.user', 'creator']);

            // 3) Apply your new filters:

            if ($request->filled('student_name')) {
                $base->whereHas(
                    'student.user',
                    fn($q) =>
                    $q->where('name', 'like', '%' . $request->student_name . '%')
                );
            }

            if ($request->filled('teacher_name')) {
                $base->whereHas(
                    'creator',
                    fn($q) =>
                    $q->where('name', 'like', '%' . $request->teacher_name . '%')
                );
            }

            if ($request->filled('result')) {
                $base->where('rs.name', $request->result);
            }

            if ($request->filled('date_from')) {
                $base->whereDate('recitations.created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $base->whereDate('recitations.created_at', '<=', $request->date_to);
            }

            // 4) (Optional) preserve your old `search_field` / `search_value` logic
            if ($field = $request->search_field && $value = $request->search_value) {
                match ($field) {
                    'student' => $base->whereHas('student.user', fn($q) => $q->where('name', 'like', "%{$value}%")),
                    'teacher' => $base->whereHas('creator', fn($q) => $q->where('name', 'like', "%{$value}%")),
                    'result' => $base->where('rs.name', $value),
                    default => null,
                };
            }

            // 5) Final pagination
            $recitations = $base
                ->orderBy('recitations.created_at', 'desc')
                ->paginate(10)
                ->withQueryString();

            return view('dashboard.recitations.index', compact('recitations', 'settings'));

        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة بيانات البحث.');

        } catch (Exception $e) {
            Log::error('Error listing recitations', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء جلب التسميعات.');
        }
    }


    public function toggleFinal(Recitation $recitation)
    {
        try {
            $studentId = $recitation->student_id;
            $page = $recitation->page;
            $activeCourseId = course_id();

            if ($recitation->is_final) {
                $recitation->is_final = false;
                $message = 'تم السماح بإعادة تسجيل الصفحة.';
            } else {
                // Finalizing: ensure no other final in active course
                $exists = Recitation::where('student_id', $studentId)
                    ->where('page', $page)
                    ->where('course_id', $activeCourseId)
                    ->where('is_final', true)
                    ->exists();

                if ($exists) {
                    throw new AccessDeniedHttpException(
                        'لا يمكن تأكيد التسجيل لأن الصفحة مُسجّلة نهائيًا بالفعل.'
                    );
                }

                $recitation->is_final = true;
                $message = 'تم تأكيد التسجيل النهائي للصفحة.';
            }

            $recitation->save();
            $stu = $recitation->student;
            $stu->update(['cashed_points' => $stu->points]);
            return redirect()
                ->route('admin.recitations.index')
                ->with('success', $message);

        } catch (AccessDeniedHttpException $e) {
            return redirect()
                ->back()
                ->with('danger', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error toggling recitation final', [
                'id' => $recitation->id,
                'error' => $e->getMessage()
            ]);
            return redirect()
                ->back()
                ->with('danger', 'حدث خطأ أثناء تعديل حالة التسجيل.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Recitation $recitation)
    {
        try {
            $recitation->load([
                'student.user',
                'creator',
                'course',
                'recitationMistakes.mistake'
            ]);

            return view('dashboard.recitations.show', compact('recitation'));

        } catch (Exception $e) {
            Log::error('Error showing recitation', [
                'id' => $recitation->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('danger', 'تعذّر عرض تفاصيل التسميع.');
        }
    }

    /**
     * Remove the specified recitation from storage.
     */
    public function destroy(Recitation $recitation)
    {
        try {
            $student = Student::findOrFail($recitation->student_id);
            $recitation->delete();

            // Recalculate and cache the student's points
            $student->update([
                'cashed_points' => $student->points
            ]);

            return redirect()->route('admin.recitations.index')
                ->with('success', 'تم حذف التسميع بنجاح.');

        } catch (Exception $e) {
            Log::error('Error deleting recitation', [
                'id' => $recitation->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء حذف التسميع.');
        }
    }
}