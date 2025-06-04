<?php

namespace App\Http\Controllers\API\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NoteController extends Controller
{
    /**
     * Teacher requests creation of a note (pending approval).
     */
    public function store(Request $request)
    {
        try {
            // 1) Validate the request
            $data = $request->validate([
                'student_token' => 'required|string|exists:students,qr_token',
                'type' => 'required|in:positive,negative',
                'reason' => 'required|string|min:5|max:200',
            ]);

            // 2) Find student & ensure teacher->student relationship
            $student = Student::where('qr_token', $data['student_token'])->firstOrFail();
            $teacher = Auth::user()->teacher;

            if ($student->circle_id !== $teacher->circle_id) {
                return response()->json([
                    'message' => 'لا يمكن إضافة ملاحظة لطالب لا ينتمي لحلقة الأستاذ.'
                ], 406);
            }

            // 3) Create the pending note
            $note = Note::create([
                'by_id' => Auth::id(),
                'student_id' => $student->id,
                'course_id' => course_id(),
                'type' => $data['type'],
                'reason' => $data['reason'],
                'value' => null,
            ]);

            return response()->json([
                'message' => 'تم إرسال طلب إضافة الملاحظة بنجاح، في انتظار موافقة الإدارة.',
                'note_id' => $note->id,
            ], 201);

        } catch (ValidationException $ve) {
            // Return the first validation error
            $error = collect($ve->errors())->flatten()->first();
            return response()->json([
                'message' => $error,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error in teacher note request', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'حدث خطأ غير متوقع أثناء إرسال طلب الملاحظة.',
            ], 500);
        }
    }
}