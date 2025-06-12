<?php

namespace App\Http\Controllers\API\Teachers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * POST /api/auth/login
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => [
                    'required',
                    'string',
                    Rule::exists('users', 'name')->whereIn('role_id', [2, 3]),
                ],
                'password' => 'required|string',
            ], [
                'name.exists' => 'الحساب غير موجود أو لا يملك صلاحية تسجيل الدخول.',
            ]);

            $user = User::where('name', $data['name'])->first();

            if (!Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'message' => 'كلمة المرور غير صحيحة.'
                ], 401);
            }

            // Determine user type and circle_id
            if ($user->role_id === 2 && $user->relationLoaded('teacher') || $user->teacher) {
                $type = 'teacher';
                $circle_id = $user->teacher?->circle_id;
            } else {
                $type = 'helper_teacher';
                $circle_id = null;
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'تم تسجيل الدخول بنجاح.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => $type,
                    'circle_id' => $circle_id,
                ],
                'token' => $token,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'خطأ في التحقق من البيانات.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Login error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'حدث خطأ غير متوقع. حاول مرة أخرى لاحقاً.',
            ], 500);
        }
    }
}
