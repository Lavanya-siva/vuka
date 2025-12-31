<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PersonalInfo;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PersonalInfoController extends Controller
{
    
    public function savePersonalInfo(Request $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        //Gate for curr user modify other-admin?
       $authUser = $request->user();   // authenticated user
       $targetUser = User::find($request->user_id);
       if (Gate::forUser($authUser)->denies('can-save-personal-info', $targetUser)) {
       return response()->json(['message' => 'Unauthorized user or verify otp'], 403);
       } // role based cond-admin can access

        // curr user-authorized?
       //Gate::authorize('can-save-personal-info', $user);
       

        try {
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
        }    catch (ValidationException $e) {
             return response()->json([
            'success' => false,
            'errors' => $e->errors()
            ], 422);
        }

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
