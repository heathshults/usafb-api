<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Helpers\AuthHelper;

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
    public function handle($request, Closure $next, ...$roles)
    {
        //if (!AuthHelper::hasRoles($request->user(), $roles)) {
        //    throw new AccessDeniedHttpException("Permission denied.");
        //}
        return $next($request);
    }
}
