<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OtpVerification;
use App\Models\PersonalInfo;

class UserController extends Controller
{   
     public function showCreateForm()
    {
        return view('auth.create-account');
    }
    // Step 1: create account + send OTP
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
            'expires_at' => now()->addMinutes(5),
        ]);

        return response()->json([
            'message' => 'OTP sent',
            'user_id' => $user->id,
            'otp' => $otp
        ]);
    }

    // Step 2: verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'otp_code' => 'required|digits:6',
        ]);

        $otpRecord = OtpVerification::where('user_id', $request->user_id)
            ->where('otp_code', $request->otp_code)
            ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid OTP'], 422);
        }

        if ($otpRecord->expires_at < now()) {
            return response()->json(['message' => 'OTP expired'], 422);
        }

        $otpRecord->verified = true;
        $otpRecord->save();

        $user = $otpRecord->user;
        $user->registration_status = 'otp_verified';
        $user->save();

        return response()->json([
            'message' => 'OTP verified successfully'
        ]);
    }

    // Step 3: get personal info (for frontend)
    public function getPersonalInfo(Request $request)
    {
        $user_id = $request->query('user_id');

        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'user' => $user
        ]);
    }

    // Optional Step 4: save personal info
    public function savePersonalInfo(Request $request)
    {
          $user = User::find($request->user_id);

    if (!$user) {
        return response()->json([
            'message' => 'User not found.'
        ], 404);
    }

    // Check registration status
    if ($user->registration_status !== 'otp_verified') {
        return response()->json([
            'message' => 'Cannot save personal info before OTP verification.'
        ], 403);
    }
        $request->validate([
            'user_id' => 'required',
            'proof_type' => 'required|in:National ID,Alien ID,Passport ID',
            'id_number' => 'required|string|unique:personal_infos,id_number',
            'kra_pin' => 'required|string|unique:personal_infos,kra_pin',
            'date_of_birth' => 'required|date',
            'nationality' => 'required|string',
            'country_residence' => 'required|string',
            'country_birth' => 'required|string',
            'gender' => 'required|in:Male,Female,Others',
            'employment_status' => 'required|in:Employed,Unemployed,SelfEmployed',
        ]);

        $personalInfo = PersonalInfo::create([
            'user_id' => $request->user_id,
            'proof_type' => $request->proof_type,
            'id_number' => $request->id_number,
            'kra_pin' => $request->kra_pin,
            'date_of_birth' => $request->date_of_birth,
            'nationality' => $request->nationality,
            'country_residence' => $request->country_residence,
            'country_birth' => $request->country_birth,
            'gender' => $request->gender,
            'employment_status' => $request->employment_status,
        ]);
        $user->registration_status = 'personal_info';
        $user->save();

        return response()->json([
            'message' => 'Personal info saved',
            'user' => $user,
            'personal_info' => $personalInfo
        ]);
    }
}
