<?php

namespace App\Providers;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
   protected $policies = [
    \App\Models\User::class => \App\Policies\UserPolicy::class,
];


    public function boot()
{
    $this->registerPolicies();

    /*Gate::define('can-save-personal-info', function ($user, $targetUser) {
        return $user->id === $targetUser->id && $targetUser->registration_status === 'otp_verified';
    });*/
    Gate::define('valid-proof-type', function ($user, $proofType) {
    $allowedTypes = ['National ID', 'Alien ID', 'Passport ID'];
    return in_array($proofType, $allowedTypes, true);
});

}
} //$user-auth()->user()
