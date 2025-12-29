<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'user_id','otp_code','sent_at','expires_at','verified','attempts'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
