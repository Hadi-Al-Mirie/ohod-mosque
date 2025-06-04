<?php

namespace App\Http\Controllers\API\Teachers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LogoutController extends Controller
{
    public function __invoke(Request $request)
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