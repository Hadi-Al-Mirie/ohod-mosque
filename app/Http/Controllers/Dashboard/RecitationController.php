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
            ], [
                'search_field.in' => 'حقل البحث غير صالح.',
                'search_value.min' => 'يجب أن لا يقل البحث عن حرف واحد.',
                'search_value.max' => 'عبارة البحث طويلة جداً (أقصى 255 حرف).',
            ]);

            $cid = course_id();

            $settings = ResultSetting::where('type', 'recitation')
                ->orderBy('min_res')
                ->get();

            // Subquery: raw_score = 100 - sum(penalty per mistake record)
            $scoreSub = DB::table('recitations')
                ->select([
                    'recitations.id',
                    DB::raw('100 - COALESCE(SUM(lm.value), 0) AS raw_score'),
                ])
                ->leftJoin('mistakes_recordes AS mr', 'mr.recitation_id', 'recitations.id')
                ->leftJoin('level_mistakes AS lm', function ($join) {
                    $join->on('lm.mistake_id', 'mr.mistake_id')
                        ->on('lm.level_id', 'recitations.level_id');
                })
                ->where('recitations.course_id', $cid)
                ->groupBy('recitations.id');

            $base = Recitation::query()
                ->select('recitations.*')
                ->where('recitations.course_id', $cid)
                ->joinSub($scoreSub, 'scores', 'scores.id', 'recitations.id')
                ->leftJoin('result_settings AS rs', function ($join) {
                    $join->on('rs.type', DB::raw("'recitation'"))
                        ->on('rs.min_res', '<=', 'scores.raw_score')
                        ->on('rs.max_res', '>=', 'scores.raw_score');
                })
                ->addSelect('rs.name AS result_name')
                ->with(['student.user', 'creator']);

            if ($field = $request->search_field) {
                $value = $request->search_value;
                if ($value) {
                    match ($field) {
                        'student' => $base->whereHas(
                            'student.user',
                            fn($q) =>
                            $q->where('name', 'like', "%{$value}%")
                        ),
                        'teacher' => $base->whereHas(
                            'creator',
                            fn($q) =>
                            $q->where('name', 'like', "%{$value}%")
                        ),
                        'result' => $base->where('rs.name', $value),
                        default => null,
                    };
                }
            }

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
