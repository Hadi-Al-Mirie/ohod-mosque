<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Course;

class EnsureNoActiveCourse
{
    /**
     * Handle an incoming request.
     * If there *is* an active course, block creation.
     */
    public function handle(Request $request, Closure $next)
    {
        // if we already have an active course, redirect away:
        if (Course::where('is_active', true)->exists()) {
            return redirect()
                ->route('admin.courses.index')
                ->with('danger', 'لا يمكنك إنشاء دورة جديدة قبل إغلاق الدورة الحالية.');
        }

        return $next($request);
    }
}