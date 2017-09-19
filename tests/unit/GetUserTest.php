<?php

namespace Tests\Unit\Users;

use Mockery;
use App\Http\Services\AuthService;
use \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use WithoutMiddleware;
use Tests\Helpers\MockHelper;
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
        $mockAuth = MockHelper::authenticationMock();
        $service = new AuthService();
        $service->setAuthentication($mockAuth);
        $data = $service->authenticatedUser(self::$validHeader);
        $this->assertEquals($data, MockHelper::user());
    }

    /**
     * Test exception thrown in get user info when token is not provided
     *
     * @return void
     */
    public function testFailureGetUser()
    {
        $this->app->instance('Auth', MockHelper::authServiceMock(false));

        $response = $this->json('GET', '/me');

        $response->seeJson([
                'error' => 'Invalid token.'
            ])->seeStatusCode(401);
    }

    /**
     * Test is Too Many request exception
     *
     * @return void
     */
    public function testIsTooManyRequestException()
    {
        $service = new AuthService();
        $this->assertTrue($service->isTooManyRequestsException(MockHelper::clientExceptionMock(429)));
    }

     /**
      * Test is not Too Many request exception
      *
      * @return void
      */
    public function testIsNotTooManyRequestException()
    {
        $service = new AuthService();
        $this->assertFalse($service->isTooManyRequestsException(MockHelper::clientExceptionMock(500)));
    }

    /**
     * Test exception thrown when retry to get user info
     * after all defined intervals
     *
     * @return void
     */
    public function testInternalExceptionGetUserExhaustedRetries()
    {
        $mockAuth = MockHelper::authenticationMock('token123', 429);
        $service = new AuthService();
        $service->setAuthentication($mockAuth);

        $this->expectException(InternalException::class);
        $service->authenticatedUser(self::$validHeader);
    }

    /**
     * Test UnauthorizedHttpException on retry when a client exception is thrown
     * not related to Too many request exception
     *
     * @return void
     */
    public function testUnauthorizedExceptionGetUserOnRetry()
    {
        $mockAuth = MockHelper::authenticationMock('token123', 500);
        $service = new AuthService();
        $service->setAuthentication($mockAuth);

        $this->expectException(UnauthorizedHttpException::class);
        $service->authenticatedUser(self::$validHeader);
    }
}
