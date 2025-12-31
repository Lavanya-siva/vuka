<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

       

        if (!$user||!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
         if ($user->registration_status !== 'otp_verified') {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your OTP before logging in'
            ], 403);
        }

       $token = $user->createToken('VukaAPI-login')->plainTextToken;


        return response()->json([
            'success' => true,
            'message' => 'Login successful, now add Personal Info',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function logout(Request $request){
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

}
