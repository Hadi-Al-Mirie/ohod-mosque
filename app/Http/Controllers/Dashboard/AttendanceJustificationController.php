<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\AttendanceJustificationRequest;
use Illuminate\Http\Request;

class AttendanceJustificationController extends Controller
{
    public function index()
    {
        $requests = AttendanceJustificationRequest::with([
            'attendance.student.user',
            'requester'
        ])->where('status', 'pending')->paginate(10);
        return view('dashboard.attendances.justifications', compact('requests'));
    }

    public function approve(AttendanceJustificationRequest $req)
    {
        if ($req->status !== 'pending') {
            return back()->with('danger', 'هذا الطلب تم معالجته مسبقاً.');
        }
        $req->attendance->update([
            'type_id' => 3,
            'justification' => $req->justification,
        ]);

        $req->update(['status' => 'approved']);
        $student=$req->attendance->student;
        $student->update(['cashed_points' => $student->points]);
        return back()->with('success', 'تم قبول تبرير الغياب.');
    }

    public function reject(AttendanceJustificationRequest $req)
    {
        $req->update(['status' => 'rejected']);
        return back()->with('danger', 'تم رفض طلب التبرير.');
    }
}
