<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Throwable;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // If already logged in—forcibly redirect
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        // Basic validation
        $request->validate([
            'name' => 'required|exists:users,name',
            'password' => 'required|min:6',
        ]);

        try {
            $user = User::where('name', $request->name)->firstOrFail();

            if (!Hash::check($request->password, $user->password)) {
                return back()->withErrors([
                    'password' => 'كلمة المرور غير صحيحة.',
                ]);
            }

            if ($user->role_id != 1) {
                return back()->withErrors([
                    'role' => 'تم رفض تسجيل الدخول، أنت لست مشرفاً.',
                ]);
            }

            // All good—log them in “remember me”
            Auth::login($user, true);

            return redirect()->route('admin.dashboard');

        } catch (Throwable $e) {
            // You can log $e->getMessage() if you want
            return back()->withErrors([
                'exception' => 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.',
            ])->withInput($request->only('name'));
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
