<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOtpVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || $user->registration_status != 'otp_verified') {
            return response()->json([
                'success' => false,
                'message' => 'OTP not verified..Access denied..'
            ], 403);
        }
    }
}
