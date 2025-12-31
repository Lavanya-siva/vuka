<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PersonalInfo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PersonalInfoController extends Controller
{
     use AuthorizesRequests;
    public function savePersonalInfo(Request $request)
    {     
       /*
        //Gate for curr user modify other-admin?
       $Authuser = $request->user();   // current user from token
       if (Gate::forUser($user)->denies('can-save-personal-info', $Authuser)) {
       return response()->json(['message' => 'Unauthorized user or verify otp'], 403);
       } // role based cond-admin can access

        // curr user-authorized?
      // Gate::authorize('can-save-personal-info', $user);
       */
        $user = $request->user(); 
         if (Gate::denies('valid-proof-type', $request->proof_type)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid proof type. Allowed types: National ID, Alien ID, Passport ID'
            ], 403);
        }
        try {
        $request->validate([
        'proof_type' => 'required',
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
        $user = $request->user();
        $personalInfo = PersonalInfo::create([
            'user_id' => $user->id,
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