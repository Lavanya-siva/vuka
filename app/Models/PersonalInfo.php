<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalInfo extends Model
{
    protected $fillable = [
        'user_id','proof_type','id_number','kra_pin','date_of_birth',
        'nationality','country_residence','country_birth','gender',
        'employment_status','status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
