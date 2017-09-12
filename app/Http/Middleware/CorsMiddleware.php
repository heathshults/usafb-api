<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * CorsMiddleware
 * Enable cross-origin resource sharing
 *
 * @package    Http
 * @subpackage Middleware
 * @author     Daylen Barban <daylen.barban@bluestarsports.com>
 */
class CorsMiddleware
{
    /**
     * Handle an incoming request.
     * Capture OPTIONS requests to enable cors
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            if ($response instanceof StreamedResponse) {
                $response->headers->set($key, $value);
            } else {
                $response->header($key, $value);
            }
        }

        if ($response instanceof StreamedResponse) {
            return $response->send();
        } else {
            return $response;
        }
    }
}
