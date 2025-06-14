<?php

namespace App\Http\Controllers\API\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Models\AttendanceJustificationRequest;
class AttendanceController extends Controller
{
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'student_token' => 'required|string|exists:students,qr_token',
                'type' => 'required|integer|in:1,4',
            ]);
            $today = Carbon::today()->toDateString();
            $student = Student::where('qr_token', $data['student_token'])
                ->firstOrFail();
            $already = Attendance::where('student_id', $student->id)
                ->whereDate('attendance_date', $today)
                ->whereIn('type_id', [1, 3, 4])
                ->exists();
            if ($already) {
                return response()->json([
                    'message' => 'هذا الطالب سبق وتسجيل حضوره أو غيابه المبرر أو تأخيره لهذا اليوم.'
                ], 409);
            }
            $user = Auth::user();

            if ($user->role_id === 2) {
                // real teacher: only their own circle
                $teacher = $user->teacher;
                if (!$teacher || $student->circle_id !== $teacher->circle_id) {
                    return response()->json([
                        'message' => 'لا يمكن تسجيل حضور لطالب لا ينتمي لحلقة الأستاذ.'
                    ], 406);
                }
            } else {
                // helper teacher: must have permission #2
                $helper = $user->helperTeacher;
                if (!$helper || !$helper->permissions->contains('id', 2)) {
                    throw new AccessDeniedHttpException('ليس لديك صلاحية تسجيل الحضور.');
                }
            }
            $attendance = Attendance::where('student_id', $student->id)
                ->where('course_id', course_id())
                ->whereDate('attendance_date', $today)
                ->first();

            if ($attendance) {
                if (in_array($attendance->type_id, [1, 3, 4], true)) {
                    return response()->json([
                        'message' => 'هذا الطالب سبق وتسجيل حضوره أو غيابه المبرر أو تأخيره لهذا اليوم.'
                    ], 409);
                }
                $attendance->update([
                    'type_id' => $data['type'],
                    'by_id' => $user->id,
                ]);
            } else {
                $attendance = Attendance::create([
                    'student_id' => $student->id,
                    'course_id' => course_id(),
                    'type_id' => $data['type'],
                    'attendance_date' => $today,
                    'by_id' => $user->id,
                ]);
            }
            $student->update(['cashed_points' => $student->points]);
            return response()->json([
                'message' => 'تم تسجيل الحضور بنجاح.',
                'date' => $today,
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                ],
            ], 200);
        } catch (ValidationException $e) {
            $error = collect($e->errors())->flatten()->first();
            return response()->json([
                'message' => $error,
            ], 422);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            \Log::error('Unexpected error in AttendanceController@justify', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'حدث خطأ غير متوقع. يرجى المحاولة مرةً أخرى.',
            ], 500);
        }
    }
    public function justify(Request $request)
    {
        try {
            $data = $request->validate([
                'student_token' => 'required|string|exists:students,qr_token',
                'date' => 'required|date|before_or_equal:today',
                'justification' => 'required|string|min:3|max:200',
            ]);

            $date = $data['date'] ?? Carbon::today()->toDateString();
            $student = Student::where('qr_token', $data['student_token'])->firstOrFail();
            $teacher = Auth::user()->teacher;
            $cid = course_id();

            // Ensure same circle
            if ($student->circle_id !== $teacher->circle_id) {
                return response()->json([
                    'message' => 'لا يمكن تقديم طلب تبرير لطالب في غير حلقة.'
                ], 406);
            }

            // Find the un-justified absence (type_id = 2)
            $attendance = Attendance::where('student_id', $student->id)
                ->whereDate('attendance_date', $date)
                ->where('course_id', $cid)
                ->where('type_id', 2)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'message' => "لا يوجد غياب غير مبرر لهذا الطالب بتاريخ {$date}."
                ], 404);
            }

            // Create a justification request
            AttendanceJustificationRequest::create([
                'attendance_id' => $attendance->id,
                'requested_by' => Auth::id(),
                'justification' => $data['justification'],
            ]);

            return response()->json([
                'message' => 'تم إرسال طلب التبرير بنجاح، في انتظار موافقة الإدارة.',
                'date' => $date,
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                ],
            ], 200);

        } catch (ValidationException $e) {
            $error = collect($e->errors())->flatten()->first();
            return response()->json(['message' => $error], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ غير متوقع أثناء تقديم طلب التبرير.',
            ], 500);
        }
    }
}