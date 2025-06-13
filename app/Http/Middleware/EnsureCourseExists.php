<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Course;

class EnsureCourseExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (is_null(active_exist())) {
            return redirect()
                ->route('admin.dashboard')
                ->with('danger', 'أنشئ دورة ثم أعد المحاولة لاحقاً.');
        }

        return $next($request);
    }
}