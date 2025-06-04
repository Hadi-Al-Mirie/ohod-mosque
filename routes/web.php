<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\CircleController;
use App\Http\Controllers\Dashboard\CourseController;
use App\Http\Controllers\Dashboard\NoteController;
use App\Http\Controllers\Dashboard\SabrController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\StudentController;
use App\Http\Controllers\Dashboard\TeacherController;
use App\Http\Controllers\Dashboard\RecitationController;
use Illuminate\Support\Facades\Route;
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
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/update', [SettingsController::class, 'update'])->name('settings.update');
    Route::middleware('courseExists')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('circles', CircleController::class);
        Route::resource('students', StudentController::class);
        Route::resource('teachers', TeacherController::class);
        Route::get('student/{student}/print', [StudentController::class, 'print'])
            ->name('students.print');
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
        Route::resource('courses', CourseController::class);
        Route::get('notes/requests', [NoteController::class, 'requests'])->name('notes.requests');
        Route::patch('notes/{note}/approve', [NoteController::class, 'approve'])
            ->name('notes.approve');
        Route::resource('notes', NoteController::class)->parameters([
            'notes' => 'note'
        ])->where(['note' => '^(?!requests$).+']);
    });
    // in routes/dashboard.php (or web.php under admin group)
    Route::resource('awqafs', AwqafController::class);

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');
});




Route::get('/list-migrations', function () {
    $migrations = [];
    $migrationsPath = database_path('migrations');

    $finder = new Finder();
    $finder->in($migrationsPath)->files()->name('*.php');

    foreach ($finder as $file) {
        $content = $file->getContents();

        // Find all schema creation blocks
        preg_match_all('/Schema::create\(.*?}\);\n?/s', $content, $schemaMatches);
        $schemas = $schemaMatches[0] ?? [];

        if (!empty($schemas)) {
            $migrations[] = [
                'filename' => $file->getFilename(),
                'schemas' => array_map('trim', $schemas)
            ];
        }
    }

    $output = '';
    foreach ($migrations as $migration) {
        $output .= "";
        foreach ($migration['schemas'] as $schema) {
            $output .= $schema . "\n";
        }
    }
    return "<pre>" . ($output ?: "No migrations found") . "</pre>";
});


Route::get('/list-models', function () {
    $modelsContent = [];
    $modelsPath = app_path('Models');

    $finder = new Finder();
    $finder->in($modelsPath)->files()->name('*.php');

    foreach ($finder as $file) {
        $content = $file->getContents();

        // Extract class declaration and body
        if (preg_match('/class\s+.*/s', $content, $matches)) {
            $classContent = $matches[0];

            // Verify the class is an Eloquent model
            $relativePath = $file->getRelativePathname();
            $className = str_replace(['.php', '/'], ['', '\\'], $relativePath);
            $fullClassName = 'App\\Models\\' . $className;

            if (class_exists($fullClassName) && is_subclass_of($fullClassName, Model::class)) {
                $modelsContent[] = $classContent;
            }
        }
    }

    $output = implode("\n\n", $modelsContent);

    return "<pre>" . ($output ?: "No models found") . "</pre>";
});