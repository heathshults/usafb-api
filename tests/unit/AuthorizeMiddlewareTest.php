<?php

namespace Tests\Unit;

use Mockery;
use App\Http\Services\AuthService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Models\Enums\Role;
use Illuminate\Http\Request;
use Tests\Helpers\MockHelper;
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
        self::$roles = Role::label(Role::SUPER_USER);
    }

    /**
     * Test function to determine if user has some roles
     * Successfull
     *
     * @return void
     */
    public function testHasRolesSuccessfull()
    {
        $roles = [Role::label(Role::SUPER_USER)];
        $hasRole = AuthHelper::hasRoles(MockHelper::user(), $roles);
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
        $roles = [Role::label(Role::ADMIN_USER)];
        $hasRole = AuthHelper::hasRoles(MockHelper::user(), $roles);
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
                return MockHelper::user();
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
                return MockHelper::user(
                    [
                        getenv('AUTH_METADATA') => [
                            'roles' => [Role::label(Role::TEST)]
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
                return MockHelper::user(
                    [
                        getenv('AUTH_METADATA') => [
                            'roles' => [Role::label(Role::PARTNER_USER)]
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
