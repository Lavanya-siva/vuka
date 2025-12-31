<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\OtpVerification;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CreateAccountController extends Controller
{
 public function createAccount(Request $request)
{
    try{
        $request->validate([
        'firstname' => 'required|string',
        'middlename' => 'nullable|string',
        'surname' => 'required|string',
        'email' => 'required|email|unique:users,email',
        'phone_no' => 'required|string',
        'password' => 'required|min:8',
    ]);
    } catch(ValidationException $e){
       return response()->json([
        'success' => false,
        'errors' => $e->errors()
    ], 422);
    }

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

    Mail::to($user->email)->send(new OtpMail($user, $otp)); // send mail-Mail.php-view

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

