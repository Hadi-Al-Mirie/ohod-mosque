<?php

// app/Http/Middleware/EnsureTeacher.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureTeacher
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user->role_id !== 2) {
            throw new AccessDeniedHttpException('هذا القسم مخصّص للأساتذة فقط.');
        }
        return $next($request);
    }
}