<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Circle;
use App\Models\Student;
use App\Models\Teacher;
class DashboardController extends Controller
{
    public function index()
    {
        $circles = Circle::count();
        $students = Student::count();
        $teachers = Teacher::count();
        return view('dashboard.index', compact('circles', 'students', 'teachers'));
    }
}
