<?php

namespace Tests\Unit\Users;

use Mockery;
use App\Http\Services\AuthService;
use \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use WithoutMiddleware;
use Tests\Helpers\AuthMockHelper;
use App\Exceptions\InternalException;

class GetUserTest extends \TestCase
{
    protected static $validHeader = [
        'Authorization' => ['Bearer token123']
    ];


    /**
     * Test sucessfull get user info when token is provided
     *
     * @return void
     */
    public function testSuccessfullGetUser()
    {
        $mockVerifier = AuthMockHelper::tokenVerifierMock();
        $service = new AuthService();
        $service->setVerifier($mockVerifier);
        $data = $service->authenticatedUser(self::$validHeader);
        $this->assertEquals($data, AuthMockHelper::user());
    }

    public function testUnauthorizedHttpExceptionWhenCoreExceptionThrown()
    {
        $mockVerifier = AuthMockHelper::tokenVerifierMock(AuthMockHelper::coreExceptionMock());
        $service = new AuthService();
        $service->setVerifier($mockVerifier);
        $this->expectException(UnauthorizedHttpException::class);
        $service->authenticatedUser(self::$validHeader);
    }

    /**
     * Test exception thrown in get user info when token is not provided
     *
     * @return void
     */
    public function testFailureGetUser()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock(false));

        $response = $this->json('GET', '/me');

        $response->seeJson([
                'error' => 'Invalid token.'
            ])->seeStatusCode(401);
    }
}
