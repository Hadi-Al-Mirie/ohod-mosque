<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Course;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCourseExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $currentCourse = Course::where('is_active', true)->first();
        if (is_null($currentCourse)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('danger', 'أنشئ دورة ثم أعد المحاولة لاحقا .');
        }

        return $next($request);
    }
}