<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'noCache' => \App\Http\Middleware\NoCache::class,
            'courseExists' => \App\Http\Middleware\EnsureCourseExists::class,
            'teacherOrHelper' => \App\Http\Middleware\EnsureTeacherOrHelper::class,
            'teacher' => \App\Http\Middleware\EnsureTeacher::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    })->create();