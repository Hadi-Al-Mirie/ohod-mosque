<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\CircleController;
use App\Http\Controllers\Dashboard\CourseController;
use App\Http\Controllers\Dashboard\NoteController;
use App\Http\Controllers\Dashboard\HelperTeacherController;
use App\Http\Controllers\Dashboard\SabrController;
use App\Http\Controllers\Dashboard\SabrHistoryController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\StudentController;
use App\Http\Controllers\Dashboard\TeacherController;
use App\Http\Controllers\Dashboard\RecitationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\RecitationHistoryController;
use App\Http\Controllers\Dashboard\LoginController;
use App\Http\Controllers\Dashboard\AttendanceController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\AttendanceJustificationController;
use App\Http\Controllers\Dashboard\AwqafController;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Finder\Finder;
Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/csrf-refresh', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');


// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('noCache');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');





// Dashbooard Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::middleware('courseExists')->group(function () {
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/update', [SettingsController::class, 'update'])->name('settings.update');
        Route::resource('users', UserController::class);
        Route::resource('circles', CircleController::class);
        Route::get('{student}/sabr-history', [SabrHistoryController::class, 'show'])->name('sabr.history');
        Route::patch(
            'recitations/{recitation}/toggle-final',
            [RecitationController::class, 'toggleFinal']
        )->name('recitations.toggleFinal');
        Route::get('{student}/recitation-history', [RecitationHistoryController::class, 'show'])
            ->name('recitation.history');
        Route::resource('students', StudentController::class);
        Route::resource('teachers', TeacherController::class);
        Route::resource('helper-teachers', HelperTeacherController::class);
        // Route::get('student/{student}/print', [StudentController::class, 'print'])
        //     ->name('students.print');
        Route::get('attendance/register-daily', [AttendanceController::class, 'registerDaily'])
            ->name('attendance.registerDaily');
        Route::get(
            'attendances/justifications',
            [AttendanceJustificationController::class, 'index']
        )
            ->name('attendances.justifications.index');
        Route::patch(
            'attendances/justifications/{req}/approve',
            [AttendanceJustificationController::class, 'approve']
        )
            ->name('attendances.justifications.approve');
        Route::patch(
            'attendances/justifications/{req}/reject',
            [AttendanceJustificationController::class, 'reject']
        )
            ->name('attendances.justifications.reject');
        Route::resource('attendances', AttendanceController::class);
        Route::resource('sabrs', SabrController::class);
        Route::resource('recitations', RecitationController::class);
        Route::get('notes/requests', [NoteController::class, 'requests'])->name('notes.requests');
        Route::patch('notes/{note}/approve', [NoteController::class, 'approve'])
            ->name('notes.approve');
        Route::resource('notes', NoteController::class)->parameters([
            'notes' => 'note'
        ])->where(['note' => '^(?!requests$).+']);
    });
    Route::get('old-course/{course}/show', [CourseController::class, 'show'])
        ->name('oldcourse.show');
    Route::get('courses', [CourseController::class, 'index'])
        ->name('courses.index');
    Route::get('courses/create', [CourseController::class, 'create'])
        ->name('courses.create')
        ->middleware('noActiveCourse');
    Route::post('courses', [CourseController::class, 'store'])->name('courses.store')
        ->middleware('noActiveCourse');
    Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::middleware('courseExists')->group(function () {
        Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::get('courses/{course}', [CourseController::class, 'show'])
            ->name('courses.show');

        Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
    });
    Route::resource('awqafs', AwqafController::class);
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');
});
