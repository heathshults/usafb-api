<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @throws UnauthorizedHttpException if user could not be authenticated.
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headers = $request->headers->all();
        $user = app('Auth')->authenticatedUser($headers);
        if (!isset($user)) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid token.');
        }
        $request->setUserResolver(
            function () use ($user) {
                return $user;
            }
        );
        return $next($request);
    }
}
