<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Awqaf;
use App\Models\ResultSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
class AwqafController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = $request->validate([
                'student' => 'nullable|string|max:255',
                'teacher' => 'nullable|string|max:255',
                'result' => 'nullable|numeric',
                'date' => 'nullable|date',
            ]);
            $cid = course_id();
            $query = Awqaf::with(['student.user', 'creator'])
                ->where('course_id', $cid);
            if ($data['student'] ?? false) {
                $query->whereHas('student.user', fn($q) => $q->where('name', 'like', "%{$data['student']}%"));
            }
            if ($data['teacher'] ?? false) {
                $query->whereHas('creator', fn($q) => $q->where('name', 'like', "%{$data['teacher']}%"));
            }
            if (isset($data['result'])) {
                $query->where('result', $data['result']);
            }
            if ($data['date'] ?? false) {
                $query->whereDate('created_at', $data['date']);
            }
            $awqafs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
            return view('dashboard.awqafs.index', compact('awqafs'));
        } catch (ValidationException $ve) {
            return back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة بيانات البحث.');
        } catch (Exception $e) {
            Log::error('AwqafController@index error', ['error' => $e->getMessage()]);
            return back()->with('danger', 'حدث خطأ أثناء جلب بيانات الأوقاف.');
        }
    }


    public function edit(Awqaf $awqaf)
    {
        return view('dashboard.awqafs.edit', compact('awqaf'));
    }

    /**
     * Update the specified awqaf in storage.
     */
    public function update(Request $request, Awqaf $awqaf)
    {
        try {
            $data = $request->validate([
                'type' => [
                    'required',
                    Rule::in(['nomination', 'retry', 'not_attend', 'rejected', 'success']),
                ],
                'result' => [
                    'exclude_unless:type,success,retry',
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                ],
            ], [
                'type.required' => 'اختر حالة سبر الأوقاف.',
                'type.in' => 'الحالة المحددة غير صالحة.',
                'result.required' => 'النتيجة مطلوبة عند اختيار النجاح أو إعادة محاولة.',
                'result.numeric' => 'النتيجة يجب أن تكون عدداً.',
                'result.min' => 'النتيجة لا يمكن أن تكون أقل من 0.',
                'result.max' => 'النتيجة لا يمكن أن تتجاوز 100.',
            ]);

            $awqaf->type = $data['type'];
            $awqaf->result = $data['result'] ?? null;
            $awqaf->save();
            $stu = $awqaf->student;
            $stu->update(['cashed_points' => $stu->points]);
            return redirect()
                ->route('admin.awqafs.index')
                ->with('success', 'تم تحديث سبر الأوقاف بنجاح.');
        } catch (ValidationException $ve) {
            return back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (Exception $e) {
            Log::error('AwqafController@update error', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('danger', 'حدث خطأ أثناء تحديث سبر الأوقاف.');
        }
    }

}
