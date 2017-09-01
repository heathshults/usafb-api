<?php

namespace Tests\Unit;

use Mockery;
use App\Http\Services\AuthService;
use \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use Illuminate\Http\Request;
use Tests\Helpers\MockHelper;


class CreateUserTest extends \TestCase
{
    private $userRequest = [
        'email'=> 'smith@gmail.com',
        'first_name'=> 'Jhon',
        'last_name'=> 'Smith',
        'city'=> '',
        'phone_number'=> '',
        'state'=> '',
        'postal_code'=> '',
        'role'=> Role::SUPER_USER
    ];

    /**
     * Mock authenticated user
     *
     * @return Mock
     */
    public function setUpMockAuthenticatedUser()
    {
        $user = [
            getenv('AUTH_METADATA') => [
                'roles' => [Role::SUPER_USER]
            ]
        ];
        return $user;
    }

    /**
     * Test Management Service creation is singlenton
     *
     * @return void
     */
    public function testSinglentonManagementClient()
    {
        $service = new AuthService();
        $mockManagement = MockHelper::managementMock();
        $service->setManagement($mockManagement);
        $this->assertEquals($service->getManagement(), $mockManagement);
    }

    /**
     * Test function that determines if a user is Super admin
     *
     * @return void
     */
    public function testIsSuperUser()
    {
        $user = MockHelper::normalizedUser();
        $service = new AuthService();
        $service->setAuthentication(MockHelper::authenticationMock());
        $this->assertTrue($service->isSuperUser($user));

        $user = [
            'user_metadata' => [
                'roles' => []
            ]
        ];
        $this->assertFalse($service->isSuperUser($user));
    }

    /**
     * Test successfull user creation
     *
     * @return void
     */
    public function testSuccefullCreateUser()
    {
        $service = new AuthService();
        $service->setManagement(MockHelper::managementMock());
        $service->setAuthentication(MockHelper::authenticationMock());

        $userCreated = $service->createUser($this->userRequest);
        $this->assertEquals($userCreated, MockHelper::userResponse());

    }

    /**
     * Test failed on user creation endpoint when email is missing
     *
     * @return void
     */
    public function testMissingEmail()
    {
        $this->app->instance('Auth', MockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', MockHelper::authenticateMiddlewareMock());

        $response = $this->json('POST', '/users', [
                'first_name' => 'test',
                'last_name' => 'test',
                'role' => Role::SUPER_USER
            ])->seeJson([
            'title' => 'Invalid Email',
        ])->seeStatusCode(400);
    }

    /**
     * Test failed on user creation endpoint when email is invalid
     *
     * @return void
     */
    public function testInvalidEmail()
    {
        $this->app->instance('Auth', MockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', MockHelper::authenticateMiddlewareMock());

        $this->json('POST', '/users', [
                'email' => 'test',
                'first_name' => 'test',
                'last_name' => 'test',
                'role' => Role::SUPER_USER
            ])->seeJson([
            'title' => 'Invalid Email',
        ])->seeStatusCode(400);
    }

    /**
     * Test failed on user creation endpoint when first name is missing
     *
     * @return void
     */
    public function testMissingFirstName()
    {
        $this->app->instance('Auth', MockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', MockHelper::authenticateMiddlewareMock());

        $this->json('POST', '/users', [
                'email' => 'test@gmail.com',
                'last_name' => 'test',
                'role' => Role::SUPER_USER
            ])->seeJson([
            'title' => 'Invalid First_name',
        ])->seeStatusCode(400);
    }

    /**
     * Test failed on user creation endpoint when last name is missing
     *
     * @return void
     */
    public function testMissingLastName()
    {
        $this->app->instance('Auth', MockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', MockHelper::authenticateMiddlewareMock());

        $this->json('POST', '/users', [
                'email' => 'test@gmail.com',
                'first_name' => 'test',
                'role' => Role::SUPER_USER
            ])->seeJson([
            'title' => 'Invalid Last_name',
        ])->seeStatusCode(400);
    }

    /**
     * Test failed on user creation endpoint when role is missing
     *
     * @return void
     */
    public function testMissingRole()
    {
        $this->app->instance('Auth', MockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', MockHelper::authenticateMiddlewareMock());

        $this->json('POST', '/users', [
                'email' => 'test@gmail.com',
                'first_name' => 'test',
                'last_name' => 'test'
            ])->seeJson([
                'title' => 'Invalid Role',
            ])->seeStatusCode(400);
    }



}
