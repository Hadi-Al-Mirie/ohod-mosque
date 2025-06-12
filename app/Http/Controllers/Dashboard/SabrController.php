<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Sabr;
use App\Models\Student;
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
            $settings = ResultSetting::where('type', 'sabr')
                ->orderBy('min_res')
                ->get();

            // Build subquery: raw_score = 100 - sum(penalty per mistake record)
            $scoreSub = DB::table('sabrs')
                ->select([
                    'sabrs.id',
                    DB::raw('100 - COALESCE(SUM(lm.value), 0) AS raw_score'),
                ])
                ->leftJoin('mistakes_recordes AS mr', 'mr.sabr_id', 'sabrs.id')
                ->leftJoin('level_mistakes AS lm', function ($join) {
                    $join->on('lm.mistake_id', 'mr.mistake_id')
                        ->on('lm.level_id', 'sabrs.level_id');
                })
                ->where('sabrs.course_id', $cid)
                ->groupBy('sabrs.id');

            $base = Sabr::query()
                ->select('sabrs.*')
                ->where('sabrs.course_id', $cid)
                ->joinSub($scoreSub, 'scores', 'scores.id', 'sabrs.id')
                ->leftJoin('result_settings AS rs', function ($join) {
                    $join->on('rs.type', DB::raw("'sabr'"))
                        ->on('rs.min_res', '<=', 'scores.raw_score')
                        ->on('rs.max_res', '>=', 'scores.raw_score');
                })
                ->addSelect('rs.name AS result_name')
                ->with(['student.user', 'creator']);

            // Apply filtering if requested
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
            Log::error('Error listing sabrs', ['error' => $e->getMessage()]);
            return back()
                ->with('danger', 'حدث خطأ أثناء جلب بيانات السبر.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sabr $sabr)
    {
        try {
            $sabr->load([
                'student.user',
                'creator',
                'course',
                'sabrMistakes.mistake'
            ]);

            return view('dashboard.sabrs.show', compact('sabr'));

        } catch (Exception $e) {
            Log::error('Error showing sabr', ['id' => $sabr->id, 'error' => $e->getMessage()]);
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
            $student = Student::findOrFail($sabr->student_id);
            $sabr->delete();

            // Recalculate cached points
            $student->update([
                'cashed_points' => $student->points
            ]);

            return redirect()->back()
                ->with('success', 'تم حذف السبر بنجاح.');

        } catch (Exception $e) {
            Log::error('Error deleting sabr', ['id' => $sabr->id, 'error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء حذف السبر.');
        }
    }
}