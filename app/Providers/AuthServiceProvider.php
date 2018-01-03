<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\AuthServiceProvider as ServiceProvider;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Policies\ClearancePolicy;
use App\Policies\CompetitionPolicy;
use App\Policies\FilePolicy;
use App\Policies\PlayerPolicy;
use App\Policies\RegistrationPolicy;
use App\Models\Clearance;
use App\Models\Competition;
use App\Models\File;
use App\Models\Player;
use App\Models\Registration;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        //Gate::policy(Registration::class, RegistrationPolicy::class);
            
        $this->app['auth']->viaRequest('api', function ($request) {
            $headers = $request->headers->all();
            try {
                $user = app('Auth')->authenticatedUser($headers);
            } catch (UnauthorizedHttpException $e) {
                return;
            }
            return $user;
        });
    }
}
