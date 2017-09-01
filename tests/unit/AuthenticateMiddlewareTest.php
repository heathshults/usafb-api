<?php

namespace Tests\Unit;

use Mockery;
use App\Http\Services\AuthService;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use Illuminate\Http\Request;
use Tests\Helpers\MockHelper;


class AuthenticateMiddlewareTest extends \TestCase
{

    /**
     * Test successfull get token from header
     *
     * @return void
     */
    public function testSuccefullTokenHeader()
    {
        $service = new AuthService();
        $headers = [
            'Authorization' => ['Bearer token123']
        ];
        $this->assertEquals('token123', $service->getHeaderToken($headers));
    }

    /**
     * Test failed get token from header when missing token
     *
     * @return void
     */
    public function testMissingTokenHeader()
    {
        $service = new AuthService();
        $headers = [];
        $this->expectException(UnauthorizedHttpException::class);
        $service->getHeaderToken($headers);
    }

    /**
     * Test success getting access token from client
     *
     * @return void
     */
    public function testAccessTokenClientSuccessful()
    {

        $service = new AuthService();
        $service->setAuthentication(MockHelper::authenticationMock());
        $this->assertEquals($service->getAccessTokenClient(), 'token123');
    }

    /**
     * Test failure getting access token from client
     *
     * @return void
     */
    public function testAccessTokenClientFailure()
    {
        $service = new AuthService();
        $service->setAuthentication(MockHelper::authenticationMock(null));
        $this->assertEquals($service->getAccessTokenClient(), null);
    }

    /**
     * Test success on requests when user is authenticated
     *
     * @return void
     */
    public function testSuccessfullRequestAuthenticatedUser()
    {
        $mockAuth = MockHelper::authServiceMock();
        $this->app->instance('Auth', $mockAuth);
        $request = Request::create('/users', 'GET', []);
        $request->headers->add(['Authorization' => ['Bearer token123']]);
        $middleware = new \App\Http\Middleware\Authenticate();
        $response = $middleware->handle(
            $request, function () {
                return ['status' => 200];
            }
        );
        $this->assertEquals(
            $response,
            ['status' => 200]
        );
    }

    /**
     * Test exception thrown when user is not authenticated
     *
     * @return void
     */
    public function testExceptionNotAuthenticatedUser()
    {
        $mockAuth = MockHelper::authServiceMock(false);
        $this->app->instance('Auth', $mockAuth);
        $request = Request::create('/users', 'GET', []);
        $request->headers->add(['Authorization' => ['Bearer token123']]);
        $middleware = new \App\Http\Middleware\Authenticate();

        $this->expectException(UnauthorizedHttpException::class);
        $response = $middleware->handle(
            $request, function () {
                /* do nothing */
            }
        );

    }

    /**
     * Test exception message when user is not authenticated
     *
     * @return void
     */
    public function testExceptionMessageNotAuthenticatedUser()
    {
        $mockAuth = MockHelper::authServiceMock(false);
        $this->app->instance('Auth', $mockAuth);
        $request = Request::create('/users', 'GET', []);
        $request->headers->add(['Authorization' => ['Bearer token123']]);
        $middleware = new \App\Http\Middleware\Authenticate();

        $this->expectExceptionMessage('Invalid token.');
        $response = $middleware->handle(
            $request, function () {
                /* do nothing */
            }
        );
    }
}
