<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|exists:users,name,role_id,4',
                'password' => 'required|string',
            ]);
            $user = User::where('name', $validated['name'])->first();
            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json(['message' => 'the pasword is not correct'], 401);
            }
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'login done successfully',
                'user' => [
                    "id" => $user->id,
                    "name" => $user->name,
                    "circle_id" => $user->student->circle_id,
                ],
                'token' => $token
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred. Please try again later.',
                'content' => $e->getMessage(),
            ], 500);
        }
    }
    public function logout()
    {
        try {
            $user = Auth::user();
            $token = $user->currentAccessToken()->delete();
            return response()->json([
                "message" => "تم تسجيل الخروج بنجاح .",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ غير متوقع , حاول مجددا مرة أخرى',
                'content' => $e->getMessage(),
            ], 500);
        }
    }
}
