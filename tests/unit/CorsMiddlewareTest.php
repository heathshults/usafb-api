<?php

namespace Tests\Unit;

use Mockery;
use Illuminate\Http\Request;

class CorsMiddlewareTest extends \TestCase
{
    /**
     * Test success on OPTIONS requests sent
     * to enable client-side cross-origin requests
     *
     * @return void
     */
    public function testSuccessfullAllowsOptionsMethod()
    {
        $request = Request::create('/rest/auth/login', 'OPTIONS', []);

        $middleware = new \App\Http\Middleware\CorsMiddleware();
        $response = $middleware->handle(
            $request, function () {

            }
        );
        $this->assertEquals(
            $response->getStatusCode(),
            200
        );
    }

    /**
     * Test success on headers set in all requests
     * to enable client-side cross-origin requests
     *
     * @return void
     */
    public function testSuccessfullSetCorsHeaders()
    {
        $request = Request::create('/rest/auth/login', 'OPTIONS', []);

        $middleware = new \App\Http\Middleware\CorsMiddleware();
        $response = $middleware->handle(
            $request, function () {

            }
        );
        $headers = $response->headers;

        $this->assertEquals(
            $headers->get('Access-Control-Allow-Origin'),
            '*'
        );
        $this->assertEquals(
            $headers->get('Access-Control-Allow-Methods'),
            'POST, GET, OPTIONS, PUT, DELETE'
        );
        $this->assertEquals(
            $headers->get('Access-Control-Allow-Credentials'),
            'true'
        );
        $this->assertEquals(
            $headers->get('Access-Control-Max-Age'),
            '86400'
        );
        $this->assertEquals(
            $headers->get('Access-Control-Allow-Headers'),
            'Content-Type, Authorization, X-Requested-With'
        );

    }

}
