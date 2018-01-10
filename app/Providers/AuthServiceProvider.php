<?php

namespace App\Providers;

use App\Exceptions\ExpiredTokenException;
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
use App\User;

use Log;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\AuthServiceProvider as ServiceProvider;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            // check if third-party authentication token provided
            if (!empty($request->headers->get('Authorization')) &&
                preg_match('/^(usafb)/', $request->headers->get('Authorization'))) {
                try {
                    //Log::debug('AuthServiceProvider viaRequest - trying to autheticate provider.');
                    $provider = app('ApiKey')->authenticate($request);
                } catch (UnauthorizedHttpException $e) {
                    return;
                }
                return $provider;
            }
            // validate standard/normal user
            try {
                $user = app('Auth')->authenticatedUser($request->headers->all());
            } catch (ExpiredTokenException $expiredEx) {
                // if expired token exception, throw it
                throw($expiredEx);
            } catch (UnauthorizedHttpException $unauthEx) {
                return;
            }
            return $user;
        });
    }
}
