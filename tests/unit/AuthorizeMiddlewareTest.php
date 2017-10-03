<?php

namespace Tests\Unit;

use Mockery;
use App\Http\Services\AuthService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Models\Enums\Role;
use Illuminate\Http\Request;
use Tests\Helpers\AuthMockHelper;
use App\Helpers\AuthHelper;


class AuthorizeMiddlewareTest extends \TestCase
{
    protected static $request;
    protected static $middleware;
    protected static $roles;

    public static function setUpBeforeClass()
    {
        self::$request = Request::create('/users', 'GET', []);
        self::$middleware = new \App\Http\Middleware\Authorize();
        self::$roles = Role::SUPER_USER;
    }

    /**
     * Test function to determine if user has some roles
     * Successfull
     *
     * @return void
     */
    public function testHasRolesSuccessfull()
    {
        $roles = [Role::SUPER_USER];
        $hasRole = AuthHelper::hasRoles(AuthMockHelper::user(), $roles);
        $this->assertTrue($hasRole);
    }

    /**
     * Test function to determine if user has some roles
     * Failure
     *
     * @return void
     */
    public function testFailedHasRoles()
    {
        $roles = [Role::ADMIN_USER];
        $hasRole = AuthHelper::hasRoles(AuthMockHelper::user(), $roles);
        $this->assertFalse($hasRole);
    }

    /**
     * Test success when user is authorized to an endpoint
     *
     * @return void
     */
    public function testSuccessfullRequestAuthorizedUser()
    {
        self::$request->setUserResolver(
            function () {
                return AuthMockHelper::user();
            }
        );
        $response = self::$middleware->handle(
            self::$request, function () {
                return ['status' => 200];
            },
            self::$roles
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
        self::$request->setUserResolver(
            function () {
                return AuthMockHelper::user(
                    [
                        getenv('AUTH_METADATA') => [
                            'roles' => [Role::TEST]
                        ]
                    ]
                );
            }
        );

        $this->expectException(AccessDeniedHttpException::class);
        self::$middleware->handle(
            self::$request, function () {
                /* do nothing */
            },
            self::$roles
        );

    }

    /**
     * Test exception message when user is not authorized
     *
     * @return void
     */
    public function testExceptionMessageNotAuthorizedUser()
    {
        self::$request->setUserResolver(
            function () {
                return AuthMockHelper::user(
                    [
                        getenv('AUTH_METADATA') => [
                            'roles' => [Role::PARTNER_USER]
                        ]
                    ]
                );
            }
        );

        $this->expectExceptionMessage('Permission denied.');
        self::$middleware->handle(
            self::$request, function () {
                /* do nothing */
            },
            self::$roles
        );
    }


}
