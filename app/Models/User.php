<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'firstname','middlename','surname','password','email','phone_no','terms_cond','registration_status'
    ];

    public function otp()
    {
        return $this->hasOne(OtpVerification::class, 'user_id');
    }

    public function personalInfo()
    {
        return $this->hasOne(PersonalInfo::class, 'user_id');
    }
    public function otpVerification()
    {
        return $this->hasOne(OtpVerification::class);
    }
}


