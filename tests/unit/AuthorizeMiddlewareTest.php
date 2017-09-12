<?php

namespace Tests\Unit;

use Mockery;
use App\Http\Services\AuthService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Models\Enums\Role;
use Illuminate\Http\Request;
use Tests\Helpers\MockHelper;


class AuthorizeMiddlewareTest extends \TestCase
{
    /**
     * Test function to determine if user has some roles
     * Successfull
     * @return void
     */
    public function testHasRolesSuccessfull()
    {
        $service = new AuthService();
        $roles = [Role::label(Role::SUPER_USER)];
        $hasRole = $service->hasRoles(MockHelper::normalizedUser(), $roles);
        $this->assertTrue($hasRole);
    }

    /**
     * Test function to determine if user has some roles
     * Failure
     * @return void
     */
    public function testFailedHasRoles()
    {
        $service = new AuthService();
        $roles = [Role::label(Role::ADMIN_USER)];
        $hasRole = $service->hasRoles(MockHelper::normalizedUser(), $roles);
        $this->assertFalse($hasRole);
    }

    /**
     * Test success when user is authorized to an endpoint
     *
     * @return void
     */
    public function testSuccessfullRequestAuthorizedUser()
    {
        $this->app->instance('Auth', MockHelper::authServiceMock());
        $request = Request::create('/users', 'GET', []);
        $middleware = new \App\Http\Middleware\Authorize();
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
     * Test exception thrown when user is not authorized
     *
     * @return void
     */
    public function testExceptionNotAuthorizedUser()
    {
        $this->app->instance('Auth', MockHelper::authServiceMock(false, false));
        $request = Request::create('/users', 'GET', []);
        $middleware = new \App\Http\Middleware\Authorize();

        $this->expectException(AccessDeniedHttpException::class);
        $response = $middleware->handle(
            $request, function () {
                /* do nothing */
            }
        );

    }

    /**
     * Test exception message when user is not authorized
     *
     * @return void
     */
    public function testExceptionMessageNotAuthorizedUser()
    {
        $this->app->instance('Auth', MockHelper::authServiceMock(false, false));
        $request = Request::create('/users', 'GET', []);
        $middleware = new \App\Http\Middleware\Authorize();

        $this->expectExceptionMessage('Permission denied.');
        $response = $middleware->handle(
            $request, function () {
                /* do nothing */
            }
        );
    }


}
