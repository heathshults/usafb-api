<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Helpers\AuthHelper;
use Log;

class Authorize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure  $next
     *
     * @throws AccessDeniedHttpException if the user does not have access
     * to this endpoint
     * @return mixed
     */
    public function handle($request, Closure $next, ...$permissions)
    {
        $user = $request->user();
        if (!is_null($user) && $user->hasRolePermissions($permissions)) {
            return $next($request);
        }
        throw new AccessDeniedHttpException("Permission denied.");
    }
}
