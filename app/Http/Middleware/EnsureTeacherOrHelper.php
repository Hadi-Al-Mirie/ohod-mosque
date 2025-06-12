<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureTeacherOrHelper
{
    /**
     * Allow only real teachers (role_id=2) or helper teachers (role_id=3).
     * Helper teachers must also have the specific permission to access recitation/attendance.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user->role_id === 2) {
            // real teacher – OK
            return $next($request);
        }

        if ($user->role_id === 3) {
            // helper teacher – must have permission 2 (“recitation”) or permission 1 (“attendance”) etc.
            if (
                !$user->helperTeacher
                || !$user->helperTeacher->permissions->pluck('id')->contains(2)
            ) {
                throw new AccessDeniedHttpException('ليس لديك الصلاحية للوصول.');
            }
            return $next($request);
        }

        throw new AccessDeniedHttpException('غير مصرح لك.');
    }
}
