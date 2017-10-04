<?php

namespace Tests\Unit\Users;

use Mockery;
use App\Http\Services\AuthService;
use \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use Illuminate\Http\Request;
use Tests\Helpers\AuthMockHelper;
use App\Models\User;


class CreateUserTest extends \TestCase
{
    private $userRequest = [
        'email'=> 'smith@gmail.com',
        'first_name'=> 'Jhon',
        'last_name'=> 'Smith',
        'city'=> '',
        'country'=> '',
        'phone_number'=> '',
        'state'=> '',
        'postal_code'=> '',
        'role'=> Role::SUPER_USER
    ];

    /**
     * Test Management Service creation is singlenton
     *
     * @return void
     */
    public function testSinglentonManagementClient()
    {
        $service = new AuthService();
        $mockManagement = AuthMockHelper::managementMock();
        $service->setManagement($mockManagement);
        $this->assertEquals($service->getManagement(), $mockManagement);
    }

    /**
     * Test successfull user creation
     *
     * @return void
     */
    public function testSuccessfullCreateUser()
    {
        $service = new AuthService();
        $service->setManagement(AuthMockHelper::managementMock());
        $service->setAuthentication(AuthMockHelper::authenticationMock());

        $userCreated = $service->createUser($this->userRequest);
        $this->assertEquals($userCreated, AuthMockHelper::user());

    }

    /**
     * Test failed on user creation endpoint when email is missing
     *
     * @return void
     */
    public function testMissingEmail()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());

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
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());

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
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());

        $this->json('POST', '/users', [
                'email' => 'test@gmail.com',
                'last_name' => 'test',
                'role' => ROLE::SUPER_USER
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
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());

        $this->json('POST', '/users', [
                'email' => 'test@gmail.com',
                'first_name' => 'test',
                'role' => ROLE::SUPER_USER
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
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());

        $this->json('POST', '/users', [
                'email' => 'test@gmail.com',
                'first_name' => 'test',
                'last_name' => 'test'
            ])->seeJson([
                'title' => 'Invalid Role',
            ])->seeStatusCode(400);
    }

    public function testInvalidRoleWhenCreatingUser()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());
        $this->json('POST', '/users', [
                'email' => 'test@gmail.com',
                'first_name' => 'test',
                'last_name' => 'test',
                'role' => 'some other role'
            ])->seeJson([
                'title' => 'Invalid Role',
            ])->seeStatusCode(400);
    }
}
