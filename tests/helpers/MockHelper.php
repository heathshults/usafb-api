<?php

namespace Tests\Helpers;

use Mockery;
use App\Models\Enums\Role;
use App\Models\User;
use Aws\Result;

class MockHelper
{
    /**
     * User profile returned by auth
     *
     * @return array
     */
    static function userResponse()
    {
        return [
            'sub' => 'auth0|123',
            'name' => 'Jhon Smith',
            'email' => 'smith@gmail.com',
            'connection' => 'connection',
            getenv('AUTH_METADATA') => [
                'first_name'=> 'Jhon',
                'last_name'=> 'Smith',
                'city'=> '',
                'phone_number'=> '',
                'state'=> '',
                'postal_code'=> '',
                'roles'=> [Role::label(Role::SUPER_USER)]
            ],
            'email_verified'=> false,
            'user_id'=> 'auth0|123',
            'picture'=> 'https://s.gravatar.com/avatar/123.png',
            'nickname'=> 'Jhon Smith',
            'identities'=> [
                'connection'=> 'Username-Password-Authentication',
                'user_id'=> '123',
                'provider'=> 'auth0',
                'isSocial'=> false
            ],
            'updated_at'=> '2017-07-24T20:45:26.793Z',
            'created_at'=> '2017-07-24T20:45:26.793Z'
        ];
    }

    /**
     * Normalized user response
     *
     * @return array
     */
    static function normalizedUser()
    {
        return [
            'id' => 'auth0|123',
            'name' => 'Jhon Smith',
            'email' => 'smith@gmail.com',
            'connection' => 'connection',
            'user_metadata' => [
                'first_name'=> 'Jhon',
                'last_name'=> 'Smith',
                'city'=> '',
                'phone_number'=> '',
                'state'=> '',
                'postal_code'=> '',
                'roles'=> [Role::label(Role::SUPER_USER)]
            ],
            'email_verified'=> false,
            'user_id'=> 'auth0|123',
            'picture'=> 'https://s.gravatar.com/avatar/123.png',
            'nickname'=> 'Jhon Smith',
            'identities'=> [
                'connection'=> 'Username-Password-Authentication',
                'user_id'=> '123',
                'provider'=> 'auth0',
                'isSocial'=> false
            ],
            'updated_at'=> '2017-07-24T20:45:26.793Z',
            'created_at'=> '2017-07-24T20:45:26.793Z'
        ];
    }


    /**
     * User Profile
     *
     * @return array
     */
    static function userRequest()
    {
        return [
            'first_name'=> 'Jhon',
            'last_name'=> 'Smith',
            'city'=> '',
            'phone_number'=> '',
            'state'=> '',
            'postal_code'=> '',
            'role'=> Role::SUPER_USER
        ];
    }

    /**
     * Mock Authentication class from Auth0 Api
     *
     * @param string $token Client access token
     *
     * @return Mock
     */
    static function authenticationMock($token = 'token123', $exceptionCode = null)
    {
        $mockAuth = Mockery::mock(\Auth0\SDK\API\Authentication::class);

        if (is_null($exceptionCode)) {
            $mockAuth->shouldReceive('userinfo')
                ->andReturn(MockHelper::userResponse());
        } else {
            $exceptionResponse = Mockery::mock(\GuzzleHttp\Psr7\Response::class);
            $exceptionResponse->shouldReceive('getStatusCode')
                ->andReturn($exceptionCode);

            $exceptionMock = Mockery::mock(\GuzzleHttp\Exception\ClientException::class);
            $exceptionMock->shouldReceive('getResponse')
                ->andReturn($exceptionResponse);

            $mockAuth->shouldReceive('userinfo')
                ->andThrow($exceptionMock);
        }
        $mockAuth->shouldReceive('dbconnections_change_password')
            ->andReturn(
                "Email sent"
            )->shouldReceive('client_credentials')
            ->andReturn(
                [
                    'access_token' => $token
                ]
            );
        return $mockAuth;
    }

    /**
     * Return a user model based from auth0 response
     *
     * @param array $data Additional fields information
     *
     * @return array
     */
    static function user($data = [], $userResponse = null)
    {
        if (is_null($userResponse)) {
            return new User(array_merge(MockHelper::userResponse(), $data));
        }

        return new User($userResponse);
    }

    /**
     * Mock Client Exception
     *
     * @param string $exceptionCode status code
     *
     * @return Mock
     */
    static function clientExceptionMock($exceptionCode)
    {
        $exceptionResponse = Mockery::mock(\GuzzleHttp\Psr7\Response::class);
        $exceptionResponse->shouldReceive('getStatusCode')
            ->andReturn($exceptionCode);

        $exceptionMock = Mockery::mock(\GuzzleHttp\Exception\ClientException::class);
        $exceptionMock->shouldReceive('getResponse')
            ->andReturn($exceptionResponse);

        return $exceptionMock;
    }

    /**
     * Mock Management class from Auth0 Api
     *
     * @param array $data New user information
     *
     * @return Mock
     */
    static function managementMock($data = [], $userList = [])
    {

        $userUpdated = array_merge(MockHelper::userResponse(), $data);
        $mockManagement = Mockery::mock(\Auth0\SDK\API\Management::class);
        $mockManagement->users = Mockery::mock(\Auth0\SDK\API\Management\Users::class);
        $mockManagement->users->shouldReceive('update')
            ->andReturn(
                $userUpdated
            )
            ->shouldReceive('create')
            ->andReturn(MockHelper::userResponse())
            ->shouldReceive('getAll')
            ->andReturn($userList);
        return $mockManagement;
    }

    /**
     * Mock Authenticate Middleware
     *
     * @return Mock
     */
    static function authenticateMiddlewareMock($hasUser = true)
    {
        $user = $hasUser ? MockHelper::user() : null;
        $mockMiddleware = Mockery::mock(App\Http\Middleware\Authenticate::class);
        $mockMiddleware->shouldReceive('handle')->once()
            ->andReturnUsing(
                function ($request, \Closure $next) use ($user) {
                    $request->setUserResolver(
                        function () use ($user) {
                            return $user;
                        }
                    );
                    return $next($request);
                }
            );
        return $mockMiddleware;
    }

    /**
     * Mock AuthService
     *
     * @return Mock
     */
    static function authServiceMock($hasUser = true, $hasRole = true)
    {
        $user = $hasUser ? MockHelper::user() : null;
        $mockAuth = Mockery::mock(App\Http\Services\AuthService::class);
        $mockAuth->shouldReceive('hasRoles')
            ->andReturn($hasRole)
            ->shouldReceive('isSuperUser')
            ->andReturn(true)
            ->shouldReceive('isAdmin')
            ->andReturn(false)
            ->shouldReceive('authenticatedUser')
            ->andReturn($user)
            ->shouldReceive('login')
            ->andReturn(
                [
                    'access_token' => '12345',
                    'expires_in'   => '0',
                    'scope'        => [],
                    'id_token'     => 'abc123',
                    'token_type'   => 'jwt',
                ]
            );
        return $mockAuth;
    }

    /**
     * Mock AwsService
     *
     * @return Mock
     */
    static function awsServiceMock()
    {
        $mockAws = Mockery::mock('App\Http\Services\AwsService')->makePartial();
        $mockAws->shouldReceive('s3PutObject')
            ->andReturn(new \Aws\Result(['ObjectURL' => 'http://xxx.xxx']))
	    ->mock();

        return $mockAws;
    }
}
