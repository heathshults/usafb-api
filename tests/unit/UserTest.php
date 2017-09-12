<?php

namespace Tests\Unit;

use Mockery;
use App\Http\Services\AuthService;
use \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use WithoutMiddleware;
use Tests\Helpers\MockHelper;

class UserTest extends \TestCase
{

    /**
     * Test successfull normalize user
     *
     * @return void
     */
    public function testSuccefullNormalizeUser()
    {
        $service = new AuthService();
        $user = MockHelper::userResponse();
        $normalizedUser = $service->normalizeUser($user);
        $this->assertEquals($normalizedUser, MockHelper::normalizedUser());
    }

    /**
     * Test sucessfull get user info when token is provided
     *
     * @return void
     */
    public function testSuccefullGetUser()
    {
        $mockAuth = MockHelper::authenticationMock();
        $service = new AuthService();
        $service->setAuthentication($mockAuth);
        $headers = [
            'Authorization' => ['Bearer token123']
        ];
        $data = $service->authenticatedUser($headers);
        $this->assertEquals($data, MockHelper::normalizedUser());
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
}
