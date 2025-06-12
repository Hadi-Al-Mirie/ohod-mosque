<?php

namespace App\Http\Controllers\API\Teachers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Note;
use App\Models\Sabr;
use App\Models\Recitation;
use App\Models\Attendance;
class HistoryController extends Controller
{
    public function __invoke()
    {
        try {
            $userId = Auth::id();

            // 1) Notes
            $notesList = Note::where('by_id', $userId)
                ->with(['student.user'])
                ->latest()
                ->get()
                ->map(fn($note) => [
                    'student_name' => $note->student->user->name,
                    'type' => $note->type,
                    'updated_at' => $note->updated_at->toDateTimeString(),
                ]);

            // 2) Recitations
            $recitationsList = Recitation::where('by_id', $userId)
                ->with(['student.user'])
                ->latest()
                ->get()
                ->map(fn($rec) => [
                    'student_name' => $rec->student->user->name,
                    'result' => $rec->calculateResult(),
                    'created_at' => $rec->created_at->toDateTimeString(),
                ]);
            // 4) Attendances
            $attendancesList = Attendance::where('by_id', $userId)
                ->with(['student.user', 'type'])
                ->latest()
                ->get()
                ->map(fn($att) => [
                    'student_name' => $att->student->user->name,
                    'attendance_type' => $att->type->name,
                    'created_at' => $att->created_at->toDateTimeString(),
                ]);

            // Return grouped history
            return response()->json([
                'notes' => $notesList,
                'recitations' => $recitationsList,
                'attendances' => $attendancesList,
            ], 200);


        } catch (\Exception $e) {
            \Log::error('Error fetching teacher history', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'حدث خطأ أثناء جلب السجل.'
            ], 500);
        }
    }
}