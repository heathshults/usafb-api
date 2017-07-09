<?php

namespace App\Http\Middleware;

use Closure;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $headers = $request->headers->all();
            app('Auth')->authenticate($headers);

            return $next($request);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}
