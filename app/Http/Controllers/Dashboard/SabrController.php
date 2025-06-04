<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Sabr;
use App\Models\Student;
use App\Models\Mistake;
use App\Models\MistakesRecorde;
use App\Models\ResultSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SabrController extends Controller
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
            $settings = ResultSetting::where('type', 'sabr')->orderBy('min_res')->get();

            //
            // Build a subquery that returns every sabr.id + its raw_score:
            //
            $scoreSub = \DB::table('sabrs')
                ->select([
                    'sabrs.id',
                    // 100 - SUM(penalty), treating missing as 0
                    \DB::raw('100 - COALESCE(SUM(lm.value * mr.quantity),0) AS raw_score'),
                ])
                ->leftJoin('mistakes_recordes AS mr', 'mr.sabr_id', 'sabrs.id')
                ->leftJoin('level_mistakes    AS lm', function ($j) {
                    $j->on('lm.mistake_id', 'mr.mistake_id')
                        ->on('lm.level_id', 'sabrs.level_id');
                })
                ->where('sabrs.course_id', $cid)
                ->groupBy('sabrs.id');
            $base = Sabr::query()
                ->select('sabrs.*')
                ->where('sabrs.course_id', $cid)
                ->joinSub($scoreSub, 'scores', 'scores.id', 'sabrs.id')
                ->leftJoin('result_settings AS rs', function ($j) {
                    $j->on('rs.type', \DB::raw("'sabr'"))
                        ->on('rs.min_res', '<=', 'scores.raw_score')
                        ->on('rs.max_res', '>=', 'scores.raw_score');
                })
                ->addSelect('rs.name as result_name')
                ->with(['student.user', 'creator']);

            // 2) Filtering
            if ($field = $request->search_field) {
                $value = $request->search_value;
                if ($value) {
                    match ($field) {
                        'student' => $base->whereHas('student.user', fn($q) => $q->where('name', 'like', "%{$value}%")),
                        'teacher' => $base->whereHas('creator', fn($q) => $q->where('name', 'like', "%{$value}%")),
                        'result' => $base->where('rs.name', $value),
                        default => null,
                    };
                }
            }

            // 3) Order first by bucket‐range (min_res) then newest
            $sabrs = $base
                ->orderBy('sabrs.created_at', 'desc')
                ->paginate(10)
                ->withQueryString();

            return view('dashboard.sabrs.index', compact('sabrs', 'settings'));
        } catch (ValidationException $ve) {
            return back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة بيانات البحث.');
        } catch (Exception $e) {
            \Log::error("SabrController@index error", ['e' => $e->getMessage()]);
            return back()->with('danger', 'حدث خطأ أثناء جلب بيانات السبر.');
        }
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $students = Student::with('user')->get();
            $mistakes = Mistake::where('type', 'sabr')->get();
            return view('dashboard.sabrs.add', compact('students', 'mistakes'));
        } catch (Exception $e) {
            Log::error('Error opening Sabr create form', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'تعذّر فتح نموذج تسجيل السبر.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'student_id' => 'required|exists:students,id',
                'juz' => 'required|array|min:1',
                'juz.*' => 'required|integer|min:1|max:30',
                'mistakes' => 'required|array',
                'mistakes.*' => 'required|integer|min:0',
            ], [
                'student_id.required' => 'اختيار الطالب مطلوب.',
                'student_id.exists' => 'الطالب المحدد غير موجود.',
                'juz.required' => 'اختيار جزء أو أكثر من القرآن مطلوب.',
                'juz.*.min' => 'رقم الجزء يجب أن يكون ≥ 1.',
                'juz.*.max' => 'رقم الجزء لا يمكن أن يتجاوز 30.',
                'mistakes.required' => 'تسجيل الأخطاء مطلوب.',
                'mistakes.*.integer' => 'عدد الأخطاء يجب أن يكون عددًا صحيحًا.',
                'mistakes.*.min' => 'عدد الأخطاء لا يمكن أن يكون سالبًا.',
            ]);
            $resultName = DB::transaction(function () use ($data) {
                $courseId = course_id();
                $creatorId = auth()->id();
                $student = Student::findOrFail($data['student_id']);
                $sabr = Sabr::create([
                    'student_id' => $data['student_id'],
                    'by_id' => $creatorId,
                    'course_id' => $courseId,
                    'level_id' => $student->level_id,
                    'juz' => json_encode($data['juz']),
                ]);
                foreach (Mistake::where('type', 'sabr')->pluck('id') as $mid) {
                    MistakesRecorde::create([
                        'sabr_id' => $sabr->id,
                        'mistake_id' => $mid,
                        'quantity' => $data['mistakes'][$mid] ?? 0,
                        'type' => 'sabr',
                    ]);
                }
                $res_name = $sabr->calculateResult();
                $student->update(['cashed_points' => $student->points]);
                return $res_name;
            });
            return redirect()
                ->route('admin.sabrs.index')
                ->with([
                    'success' => 'تم تسجيل السبر بنجاح.',
                    'result_name' => $resultName,
                ]);
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'يرجى تصحيح الأخطاء أدناه.');
        } catch (Exception $e) {
            Log::error('Error storing Sabr', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('danger', 'حدث خطأ أثناء تسجيل السبر.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sabr $sabr)
    {
        try {
            $sabr->load(['student.user', 'creator', 'course', 'sabrMistakes.mistake.levels']);
            return view('dashboard.sabrs.show', compact('sabr'));
        } catch (Exception $e) {
            Log::error('Error showing Sabr', ['id' => $sabr->id, 'error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'تعذّر عرض تفاصيل السبر.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sabr $sabr)
    {
        try {
            $student = Student::find($sabr->student_id);
            $sabr->delete();
            $student->update(['cashed_points' => $student->points]);
            return redirect()->back()
                ->with('success', 'تم حذف السبر بنجاح.');
        } catch (Exception $e) {
            Log::error('Error deleting Sabr', ['id' => $sabr->id, 'error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء حذف السبر.');
        }
    }
}
