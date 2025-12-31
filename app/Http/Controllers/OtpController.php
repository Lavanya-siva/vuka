<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class OtpController extends Controller
{
    public function verifyOtp(Request $request)
{
    $request->validate([
        'user_id' => 'required',
        'otp_code' => 'required|digits:6',
    ]);

    $maxChance = 3;

    // get latest OTP for this user
    $otpRecord = OtpVerification::where('user_id', $request->user_id)
        ->latest()
        ->first();

    if (!$otpRecord) {
        return response()->json(['message' => 'No OTP found. Please request a new one.'], 404);
    }

    // Check max attempts
    if ($otpRecord->attempts >= $maxChance) {
        return response()->json([
            'success' => false,
            'message' => 'Too many attempts. Please try again later.'
        ], 429); 
    }

    // Check expiration
    if ($otpRecord->expires_at < now()) {
        return response()->json(['message' => 'OTP expired'], 422);
    }

    // Check OTP
    if ($otpRecord->otp_code != $request->otp_code) {
        $otpRecord->increment('attempts'); // increment attempts on wrong OTP
        return response()->json(['message' => 'Invalid OTP'], 422);
    }

    $otpRecord->verified = true;
    $otpRecord->attempts = 0; 
    $otpRecord->save();

    $otpRecord->user->registration_status = 'otp_verified';
    $otpRecord->user->save();

    return response()->json([
        'success' => true,
        'message' => 'OTP verified successfully'
    ], 200);
}

    public function resendOtp(Request $request)
    {
        $request->validate([
            'user_id'=>'required',
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 401);
        }
        
        //already verified
        if ($user->registration_status === 'otp_verified') {
            return response()->json([
                'message' => 'OTP already verified'
            ], 400);
        }

        //generate new otp
        $otp = rand(100000, 999999);
        $otpRecord = OtpVerification::where('user_id', $request->user_id)
            ->first();

        //  save new otp in db
        $otpRecord->otp_code = $otp;
        $otpRecord->expires_at = now()->addMinutes(10);
        $otpRecord->save();

        // send otp
         Mail::send('emails.otp-verification', [
        'user' => $user,
        'otp' => $otp
        ], function ($message) use ($user) {
        $message->to($user->email)
                ->subject('Your OTP Verification Code-Resend');
        });
        return response()->json([
            'message' => 'OTP resent successfully'
        ]);
    }
}
