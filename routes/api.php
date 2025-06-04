<?php

use App\Http\Controllers\API\Teachers\AttendanceController;
use App\Http\Controllers\APi\Teachers\CircleController;
use App\Http\Controllers\API\Teachers\LoginController;
use App\Http\Controllers\API\Teachers\LogoutController;
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
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('teacher')->name('teacher.')->group(function () {
        Route::get('circle/info', [CircleController::class, 'info']);
        Route::get('circle/students', [CircleController::class, 'students']);
        Route::get('student/info', [StudentController::class, 'info']);
        Route::post('attendance/register', [AttendanceController::class, 'register']);
        Route::post('attendance/justify', [AttendanceController::class, 'justify']);
        Route::post('note/create', [NoteController::class, 'store'])
            ->name('notes.store');
        Route::get('logout', LogoutController::class);
    });
});
