<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PersonalInfo;

class PersonalInfoController extends Controller
{
    
    public function savePersonalInfo(Request $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->registration_status !== 'otp_verified') {
            return response()->json([
                'message' => 'Cannot save personal info before OTP verification.'
            ], 403);
        }
        // otp verified
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
