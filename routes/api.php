<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CreateAccountController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PersonalInfoController;


Route::prefix('user')->group(function () {
    Route::post('create-account', [CreateAccountController::class, 'createAccount']);
    Route::post('login', [AuthController::class, 'login']); 
});

Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::post('verify-otp', [OtpController::class, 'verifyOtp']);
    Route::post('personal-info', [PersonalInfoController::class, 'savePersonalInfo']);
    Route::post('resend-otp', [OtpController::class, 'resendOtp']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Email verification routes
    Route::get('email/verify', function (Request $request) {
        return response()->json([
            'message' => 'Email verification required'
        ]);
    });

    Route::get('email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully'
        ]);
    })->middleware('signed')->name('verification.verify');

    Route::post('email/verification-notification', function (Request $request) {
        if (!$request->user()) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json([
            'message' => 'Verification link sent'
        ]);
    });
});
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API route not found'
    ], 404);
});

