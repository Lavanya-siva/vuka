<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }

    if ($user->registration_status !== 'otp_verified') {
        return response()->json([
            'success' => false,
            'message' => 'Please verify your OTP before logging in'
        ], 403);
    }

    if (!Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    Auth::login($user);
    $request->session()->regenerate();

    return response()->json([
        'success' => true,
        'message' => 'Login successful, now add Personal Info',
        'user' => $user
    ], 200);
}

}