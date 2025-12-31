<?php

namespace App\Providers;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot()
{
    $this->registerPolicies();

    // Gate 1: Check if user can save personal info based on registration_status
    Gate::define('can-save-personal-info', function ($user, $targetUser) {
        return $user->id === $targetUser->id && $targetUser->registration_status === 'otp_verified';
    });
}
} //$user-auth()->user()
