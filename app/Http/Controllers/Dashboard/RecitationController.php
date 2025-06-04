<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Recitation;
use App\Models\Student;
use App\Models\Mistake;
use App\Models\MistakesRecorde;
use App\Models\ResultSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
            // 1) Validate inputs
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

            $scoreSub = DB::table('recitations')
                ->select([
                    'recitations.id',
                    DB::raw('100 - COALESCE(SUM(lm.value * mr.quantity), 0) AS raw_score'),
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
            $field = $request->search_field;
            if ($field) {
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $students = Student::with('user')->get();
            $mistakes = Mistake::where('type', 'recitation')->get();
            return view('dashboard.recitations.add', compact('students', 'mistakes'));
        } catch (Exception $e) {
            Log::error('Error opening recitation form', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'تعذّر فتح نموذج تسجيل التسميع.');
        }
    }

    /**
     * Store a newly created recitation in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'student_id' => 'required|exists:students,id',
                'page' => 'required|integer|min:1|max:604',
                'mistakes' => 'required|array',
                'mistakes.*' => 'required|integer|min:0',
            ], [
                'student_id.required' => 'اختيار الطالب مطلوب.',
                'student_id.exists' => 'الطالب المحدد غير موجود.',
                'page.required' => 'رقم الصفحة مطلوب.',
                'page.integer' => 'رقم الصفحة يجب أن يكون عددًا.',
                'page.min' => 'رقم الصفحة يجب أن يكون على الأقل 1.',
                'page.max' => 'رقم الصفحة لا يمكن أن يتجاوز 604.',
                'mistakes.required' => 'تسجيل الأخطاء مطلوب.',
                'mistakes.*.integer' => 'عدد الأخطاء يجب أن يكون رقمًا صحيحًا.',
                'mistakes.*.min' => 'عدد الأخطاء لا يمكن أن يكون سلبيًا.',
            ]);
            $resultName = DB::transaction(function () use ($data) {
                $courseId = course_id();
                $creatorId = auth()->id();
                $student = Student::find($data['student_id']);
                $rec = Recitation::create([
                    'student_id' => $data['student_id'],
                    'by_id' => $creatorId,
                    'course_id' => $courseId,
                    'page' => $data['page'],
                    'level_id' => $student->level_id,
                ]);
                foreach (Mistake::where('type', 'recitation')->pluck('id') as $mid) {
                    MistakesRecorde::create([
                        'recitation_id' => $rec->id,
                        'mistake_id' => $mid,
                        'quantity' => $data['mistakes'][$mid] ?? 0,
                        'type' => 'recitation',
                    ]);
                }
                $res_name = $rec->calculateResult();
                $student->update(['cashed_points' => $student->points]);
                return $res_name;
            });
            return redirect()
                ->route('admin.recitations.index')
                ->with([
                    'success' => 'تم تسجيل التسميع بنجاح.',
                    'result_name' => $resultName,
                ]);
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'يرجى تصحيح الأخطاء أدناه.');
        } catch (Exception $e) {
            Log::error('Error storing recitation', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('danger', 'حدث خطأ أثناء تسجيل التسميع.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Recitation $recitation)
    {
        try {
            $recitation->load(['student.user', 'creator', 'course', 'recitationMistakes.mistake']);
            return view('dashboard.recitations.show', compact('recitation'));
        } catch (Exception $e) {
            Log::error('Error showing recitation', ['id' => $recitation->id, 'error' => $e->getMessage()]);
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
            $student = Student::find($recitation->student_id);
            $recitation->delete();
            $student->update(['cashed_points' => $student->points]);
            return redirect()->route('admin.recitations.index')
                ->with('success', 'تم حذف التسميع بنجاح.');
        } catch (Exception $e) {
            Log::error('Error deleting recitation', ['id' => $recitation->id, 'error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء حذف التسميع.');
        }
    }
}
