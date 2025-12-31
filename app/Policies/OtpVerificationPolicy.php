<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OtpVerification;

class OtpVerificationPolicy
{
    public function verify(User $authUser, OtpVerification $otp)
    {
        return $authUser->id === $otp->user_id;
    }

}
