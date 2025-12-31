<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\OtpVerification;
use Illuminate\Auth\Events\Registered;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;


class CreateAccountController extends Controller
{
 public function createAccount(Request $request)
{
    $request->validate([
        'firstname' => 'required|string',
        'middlename' => 'nullable|string',
        'surname' => 'required|string',
        'email' => 'required|email|unique:users,email',
        'phone_no' => 'required|string',
        'password' => 'required|min:8',
    ]);

    $user = User::create([
        'firstname' => $request->firstname,
        'middlename' => $request->middlename,
        'surname' => $request->surname,
        'email' => $request->email,
        'phone_no' => $request->phone_no,
        'password' => Hash::make($request->password),
        'terms_cond' => true,
    ]);

    $otp = rand(100000, 999999);

    OtpVerification::create([
        'user_id' => $user->id,
        'otp_code' => $otp,
        'sent_at' => now(),
        'expires_at' => now()->addMinutes(10),
    ]);

    Mail::to($user->email)->send(new OtpMail($user, $otp));

    $token = $user->createToken('VukaAPI-CreateAccount')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Account created. Verification email sent.',
        'user_id' => $user->id,
        'access_token' => $token,
        'token_type' => 'Bearer'
    ], 201);
}

}

