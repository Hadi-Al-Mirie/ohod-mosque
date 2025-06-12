<?php

use App\Http\Controllers\API\Student\AuthController;
use App\Http\Controllers\API\Student\InfoController;
use App\Http\Controllers\API\Teachers\AttendanceController;
use App\Http\Controllers\API\Teachers\CircleController;
use App\Http\Controllers\API\Teachers\HistoryController;
use App\Http\Controllers\API\Teachers\LoginController;
use App\Http\Controllers\API\Teachers\LogoutController;
use App\Http\Controllers\API\Teachers\RecitationController;
use App\Http\Controllers\API\Teachers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Teachers\NoteController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/test', function () {
    return response()->json(['hi'], 200);
});
Route::post('/teacher/login', LoginController::class);

Route::middleware('auth:sanctum')->prefix('teacher')->name('teacher.')->group(function () {

    // ———————————————————————————————
    // Group A: teacher OR helper-with-permission
    Route::middleware('teacherOrHelper')->group(function () {
        Route::get('/recitation/check', [RecitationController::class, 'check']);
        Route::post('/recitation/store', [RecitationController::class, 'store']);
        Route::post('/attendance/register', [AttendanceController::class, 'register']);
        Route::get('logout', LogoutController::class);
    });

    // ———————————————————————————————
    // Group B: teachers ONLY
    Route::middleware('teacher')->group(function () {
        Route::get('history', HistoryController::class);
        Route::get('circle/info', [CircleController::class, 'info']);
        Route::get('circle/students', [CircleController::class, 'students']);
        Route::get('circle/students/{student}/basic-info', [CircleController::class, 'basicInfo'])
            ->missing(fn() => response()->json(['message' => 'الطالب غير موجود.'], 404));
        Route::get('student/info', [StudentController::class, 'info']);
        Route::post('attendance/justify', [AttendanceController::class, 'justify']);
        Route::post('note/create', [NoteController::class, 'store'])->name('notes.store');
    });

});





Route::post('/student/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('student')->name('student.')->group(function () {
        Route::get('/info', [InfoController::class, 'show'])->name('show');
        Route::get('logout', [AuthController::class, 'logout']);
    });
});