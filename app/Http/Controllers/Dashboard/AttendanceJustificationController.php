<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\AttendanceJustificationRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
class AttendanceJustificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_name' => 'nullable|string|max:100|min:1',
                'teacher_name' => 'nullable|string|max:100|min:1',
                'date' => 'nullable|date',
            ]);

            // 2) Build base query
            $query = AttendanceJustificationRequest::with([
                'attendance.student.user',
                'requester',
            ])->where('status', 'pending');

            // 3) Apply filters if present
            if (!empty($validated['student_name'])) {
                $query->whereHas('attendance.student.user', function ($q) use ($validated) {
                    $q->where('name', 'like', '%' . $validated['student_name'] . '%');
                });
            }

            if (!empty($validated['teacher_name'])) {
                $query->whereHas('requester', function ($q) use ($validated) {
                    $q->where('name', 'like', '%' . $validated['teacher_name'] . '%');
                });
            }

            if (!empty($validated['date'])) {
                $query->whereHas('attendance', function ($q) use ($validated) {
                    $q->whereDate('attendance_date', $validated['date']);
                });
            }


            // 4) Paginate + preserve all query string parameters
            $requests = $query
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString();

            // 5) Return view
            return view('dashboard.attendances.justifications', compact('requests'));

        } catch (ValidationException $ve) {
            // Return back to the same page with validation errors
            return back()
                ->withErrors($ve->errors())
                ->withInput()
                ->with('danger', 'تأكد من صحة بيانات البحث.');
        } catch (\Exception $e) {
            \Log::error('AttendanceJustifications@index error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Could redirect or show an error page
            return back()
                ->with('danger', 'حدث خطأ أثناء جلب طلبات تبرير الغياب.');
        }
    }


    public function approve(AttendanceJustificationRequest $req)
    {
        try {
            if ($req->status !== 'pending') {
                return back()->with('danger', 'هذا الطلب تم معالجته مسبقاً.');
            }
            $req->attendance->update([
                'type_id' => 3,
                'justification' => $req->justification,
            ]);

            $req->update(['status' => 'approved']);
            $student = $req->attendance->student;
            $student->update(['cashed_points' => $student->points]);
            return back()->with('success', 'تم قبول تبرير الغياب.');
        } catch (\Exception $e) {
            \Log::error('AttendanceJustifications@approve error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Could redirect or show an error page
            return back()
                ->with('danger', 'حدث خطأ أثناء تحديث طلب تبرير الغياب.');
        }
    }

    public function reject(AttendanceJustificationRequest $req)
    {
        try {
            $req->delete();
            return back()->with('danger', 'تم رفض طلب التبرير.');
        } catch (\Exception $e) {
            \Log::error('AttendanceJustifications@reject error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Could redirect or show an error page
            return back()
                ->with('danger', 'حدث خطأ أثناء تحديث طلب تبرير الغياب.');
        }
    }

    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (count($ids)) {
                AttendanceJustificationRequest::whereIn('id', $ids)->delete();
                return redirect()->route('admin.attendances.justifications.index')
                    ->with('success', 'تم حذف طلبات تبرير الغياب المحددة بنجاح.');
            }
            return back()->with('warning', 'لم يتم اختيار أي طلب تبرير غياب.');
        } catch (\Exception $e) {
            Log::error('Error deleting bulk attendances.justifications', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء حذف طلبات تبرير الغياب المحددة.');
        }
    }
}
