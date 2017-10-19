<?php

namespace Tests\Unit\Users;

use Mockery;
use App\Http\Services\AuthService;
use \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Models\Enums\Role;
use Tests\Helpers\AuthMockHelper;
use Illuminate\Http\Request;

class UpdateUserTest extends \TestCase
{
    protected static $userId;

    public static function setUpBeforeClass()
    {
        self::$userId = AuthMockHelper::user()->getId();
    }

    /**
     * Test successfull user update
     *
     * @return void
     */
    public function testSuccefullUpdateUser()
    {
        $data = [
            'first_name' => "Jhon",
            'email' => 'john.miler@gmail.com'
        ];
        $service = new AuthService();
        $service->setManagement(AuthMockHelper::managementMock($data));
        $service->setAuthentication(AuthMockHelper::authenticationMock());

        $updatedUser = $service->updateUser(self::$userId, $data);

        $this->assertEquals($updatedUser->getFirstName(), $data['first_name']);
        $this->assertEquals($updatedUser->getEmail(), $data['email']);
        $this->assertEquals($updatedUser->getNickname(), AuthMockHelper::userResponse()['nickname']);
    }

    /**
     * Test successfull block user
     *
     * @return void
     */
    public function testSuccefullBlockUser()
    {
        $data = [
            'blocked' => true
        ];
        $service = new AuthService();
        $service->setManagement(AuthMockHelper::managementMock($data));
        $service->setAuthentication(AuthMockHelper::authenticationMock());

        $updatedUser = $service->updateUser(self::$userId, $data);

        $this->assertEquals($updatedUser->getStatus(), 'Disabled');
    }

    /**
     * Test successfull unblock user
     *
     * @return void
     */
    public function testSuccefullUnblockUser()
    {
        $data = [
            'blocked' => false
        ];
        $service = new AuthService();
        $service->setManagement(AuthMockHelper::managementMock($data));
        $service->setAuthentication(AuthMockHelper::authenticationMock());

        $updatedUser = $service->updateUser(self::$userId, $data);

        $this->assertEquals($updatedUser->getStatus(), 'Enabled');
    }

    /**
     * Test failure user update when id is missing
     *
     * @return void
     */
    public function testFailedMissingId()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $this->json('PUT', '/users')
            ->seeJson(
                [
                    'error' => 'Bad Request'
                ]
            )
            ->seeStatusCode(400);
    }

    /**
     * Test failure user update when data is empty
     *
     * @return void
     */
    public function testFailedMissingData()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $this->json('PUT', '/users/auth0|123')
            ->seeJson(
                ['error' => 'Data required.']
            )
            ->seeStatusCode(400);
    }

    /**
     * Test exception thrown when intended change user role
     *
     * @return void
     */
    public function testFailedUpdateRole()
    {
        $service = new AuthService();
        $data = [
            'role' => 1
        ];
        $service->setManagement(AuthMockHelper::managementMock($data));
        $service->setAuthentication(AuthMockHelper::authenticationMock());

        $this->expectException(BadRequestHttpException::class);
        $response = $service->updateUser(self::$userId, $data);
    }

    /**
     * Test exception message thrown when intended change user role
     *
     * @return void
     */
    public function testFailedUpdateRoleMessage()
    {
        $service = new AuthService();
        $data = [
            'role' => 1
        ];
        $service->setManagement(AuthMockHelper::managementMock($data));
        $service->setAuthentication(AuthMockHelper::authenticationMock());

        $this->expectExceptionMessage("Role can not be changed.");
        $response = $service->updateUser(self::$userId, $data);
    }

    /**
     * Test failure user update when last name is empty
     *
     * @return void
     */
    public function testFailedEmptyLastName()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $response = $this->json('PUT', '/users/auth0|123', [
            'last_name' => ''
        ])
            ->seeJson(
                [
                    'title' => 'Invalid Last_name'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when phone number is not numeric
     *
     * @return void
     */
    public function testFailedInvalidPhoneNumber()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $response = $this->json('PUT', '/users/auth0|123', [
            'phone_number' => 'test'
        ])
            ->seeJson(
                [
                    'title' => 'Invalid Phone_number'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when is email is blank
     *
     * @return void
     */
    public function testFailedEmptyEmail()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $response = $this->json('PUT', '/users/auth0|123', [
            'email' => ''
        ])
            ->seeJson(
                [
                    'title' => 'Invalid Email'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when is email is not valid
     *
     * @return void
     */
    public function testFailedInvalidEmail()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $response = $this->json('PUT', '/users/auth0|123', [
            'email' => 'test'
        ])
            ->seeJson(
                [
                    'title' => 'Invalid Email'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when first name is empty
     *
     * @return void
     */
    public function testFailedEmptyFirstName()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $response = $this->json('PUT', '/users/auth0|123', [
            'first_name' => ''
        ])
            ->seeJson(
                [
                    'title' => 'Invalid First_name'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when password is too short
     *
     * @return void
     */
    public function testFailedShortPassword()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $password = substr(hash('sha512', rand()), 0, 7);
        $response = $this->json('PUT', '/users/auth0|123', [
            'password' => $password
        ])
            ->seeJson(
                [
                'error' => 'The password must be at least 8 characters.'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when password is too week
     * Should have at least one of this symbols @#$%*
     * and one lowwercase letter
     *
     * @return void
     */
    public function testFailedWeakPasswordMissingSymbolAndLowercaseLetter()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $password = substr(hash('sha512', rand()), 0, 8);
        $password = preg_replace("/[^a-z]/", "A", $password);
        $password = preg_replace("/[@#$%*]/", "1", $password);

        $response = $this->json('PUT', '/users/auth0|123', [
            'password' => $password
        ])
            ->seeJson(
                [
                'error' => 'Password is too weak.'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when password is too week
     * Should have at least one of this symbols @#$%*
     * and one uppercase letter
     *
     * @return void
     */
    public function testFailedWeakPasswordMissingSymbolAndUppercase()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $password = substr(hash('sha512', rand()), 0, 8);
        $password = preg_replace("/[^A-Z]/", "a", $password);
        $password = preg_replace("/[@#$%*]/", "1", $password);

        $response = $this->json('PUT', '/users/auth0|123', [
            'password' => $password
        ])
             ->seeJson(
                [
                'error' => 'Password is too weak.'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when password is too week
     * Should have at least one of this symbols @#$%*
     * and one number
     *
     * @return void
     */
    public function testFailedWeakPasswordMissingSymbolAndNumber()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $password = substr(hash('sha512', rand()), 0, 8);
        $password = preg_replace("/[^0-9]/", "a", $password);
        $password = preg_replace("/[@#$%*]/", "A", $password);

        $response = $this->json('PUT', '/users/auth0|123', [
            'password' => $password
        ])
            ->seeJson(
                [
                'error' => 'Password is too weak.'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when password is too week
     * Should have at least one lowercase and
     * one uppercase letter
     *
     * @return void
     */
    public function testFailedWeakPasswordMissingLowercaseAndUppercaseLetter()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $password = substr(hash('sha512', rand()), 0, 8);
        $password = preg_replace("/[^a-zA-Z]/", "*", $password);

        $response = $this->json('PUT', '/users/auth0|123', [
            'password' => $password
        ])
            ->seeJson(
                [
                'error' => 'Password is too weak.'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when password is too week
     * Should have at least one lowercase letter
     * and one number
     *
     * @return void
     */
    public function testFailedWeakPasswordMissingLetterAndNumber()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $password = substr(hash('sha512', rand()), 0, 8);
        $password = preg_replace("/[^a-z]/", "A", $password);
        $password = preg_replace("/[^0-9]/", "*", $password);

        $response = $this->json('PUT', '/users/auth0|123', [
            'password' => $password
        ])
            ->seeJson(
                [
                'error' => 'Password is too weak.'
                ]
            )->seeStatusCode(400);
    }

    /**
     * Test failure user update when password is too week
     * Should have at least one uppercase letter
     * and one number
     *
     * @return void
     */
    public function testFailedWeakPasswordMissingUppercaseAndNumber()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());

        $password = substr(hash('sha512', rand()), 0, 8);
        $password = preg_replace("/[^A-Z]/", "a", $password);
        $password = preg_replace("/[^0-9]/", "*", $password);

        $response = $this->json('PUT', '/users/auth0|123', [
            'password' => $password
        ])
            ->seeJson(
                [
                'error' => 'Password is too weak.'
                ]
            )->seeStatusCode(400);
    }
}
