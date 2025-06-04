<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'search' => 'nullable|string|max:255|min:1'
            ]);
            $query = Note::with(['student.user', 'creator'])
                ->where('status', 'approved');

            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('reason', 'LIKE', "%{$search}%")
                        ->orWhereHas('student.user', fn($q) => $q->where('name', 'LIKE', "%{$search}%"))
                        ->orWhereHas('creator', fn($q) => $q->where('name', 'LIKE', "%{$search}%"));
                });
            }

            $notes = $query->orderBy('created_at', 'desc')->paginate(10);

            return view('dashboard.notes.index', compact('notes'));
        } catch (Exception $e) {
            Log::error('Error loading approved notes', ['error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'حدث خطأ أثناء جلب الملاحظات.');
        }
    }

    public function requests(Request $request)
    {
        try {
            $query = Note::with(['student.user', 'creator'])
                ->where('status', 'pending');

            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('cause', 'LIKE', "%{$search}%")
                        ->orWhereHas('student.user', fn($q) => $q->where('name', 'LIKE', "%{$search}%"))
                        ->orWhereHas('creator', fn($q) => $q->where('name', 'LIKE', "%{$search}%"));
                });
            }

            $notes = $query->orderBy('created_at', 'desc')->paginate(10);

            return view('dashboard.notes.requests', compact('notes'));
        } catch (Exception $e) {
            Log::error('Error loading pending notes', ['error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'حدث خطأ أثناء جلب طلبات الملاحظات.');
        }
    }

    public function show(Note $note)
    {
        try {
            $note->load(['student.user', 'creator']);
            return view('dashboard.notes.show', compact('note'));
        } catch (Exception $e) {
            Log::error('Error showing note', ['id' => $note->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'حدث خطأ أثناء عرض الملاحظة.');
        }
    }

    public function create()
    {
        try {
            $students = Student::with('user')->get();
            return view('dashboard.notes.add', compact('students'));
        } catch (Exception $e) {
            Log::error('Error loading note creation form', ['error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'حدث خطأ أثناء التحضير لإضافة ملاحظة.');
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'student_id' => 'required|exists:students,id',
                'type' => 'required|string|in:negative,positive',
                'reason' => 'required|string|max:255',
                'value' => 'required|integer|min:0',
            ], [
                'student_id.required' => 'اختيار الطالب مطلوب.',
                'student_id.exists' => 'الطالب المحدد غير موجود.',
                'type.required' => 'نوع الملاحظة مطلوب.',
                'type.in' => 'نوع الملاحظة غير صالح.',
                'reason.required' => 'سبب الملاحظة مطلوب.',
                'value.required' => 'قيمة الملاحظة مطلوبة.',
            ]);

            $data['by_id'] = auth()->id();
            $data['status'] = 'approved';
            $data['course_id'] = course_id();

            if ($data['type'] === 'negative') {
                $data['value'] *= -1;
            }

            Note::create($data);
            $student = Student::findOrFail($request->input('student_id'));
            $student->update(['cashed_points' => $student->points]);
            return redirect()->route('admin.notes.index')
                ->with('success', 'تم تسجيل الملاحظة بنجاح.');
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (Exception $e) {
            Log::error('Error storing note', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('danger', 'حدث خطأ غير متوقع أثناء تسجيل الملاحظة.');
        }
    }

    public function destroy(Note $note)
    {
        try {
            if ($note->status == 'pending')
                $b = true;
            else
                $b = false;
            $student = Student::findOrFail($note->student_id);
            $note->delete();
            $student->update(['cashed_points' => $student->points]);
            if ($b == false) {
                return redirect()->route('admin.notes.index')
                    ->with('success', 'تم حذف الملاحظة بنجاح.');
            } else {
                return redirect()->route('admin.notes.requests')
                    ->with('success', 'تم حذف الملاحظة بنجاح.');
            }
        } catch (Exception $e) {
            Log::error('Error deleting note', ['id' => $note->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'حدث خطأ أثناء حذف الملاحظة.');
        }
    }

    public function approve(Request $request, Note $note)
    {
        try {
            $data = $request->validate([
                'value' => 'required|integer|min:0',
            ], [
                'value.required' => 'القيمة مطلوبة.',
                'value.integer' => 'القيمة يجب أن تكون عددًا صحيحًا.',
            ]);

            if ($note->type === 'negative') {
                $data['value'] *= -1;
            }

            $note->update([
                'value' => $data['value'],
                'status' => 'approved',
            ]);
            $student = $note->student;
            $student->update(['cashed_points' => $student->points]);
            return redirect()->route('admin.notes.requests')
                ->with('success', 'تم الموافقة على الملاحظة وتحديث قيمتها بنجاح.');
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withInput()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة القيمة المدخلة.');
        } catch (Exception $e) {
            Log::error('Error approving note', ['id' => $note->id, 'error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء الموافقة على الملاحظة.');
        }
    }
}