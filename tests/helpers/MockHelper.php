<?php

namespace Tests\Helpers;

use Mockery;
use App\Models\Enums\Role;

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
                'roles'=> [Role::label(Role::USAFB_ADMIN)]
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
            'role'=> Role::USAFB_ADMIN
        ];
    }

    /**
     * Mock Authentication class from Auth0 Api
     *
     * @param string $token Client access token
     *
     * @return Mock
     */
    static function authenticationMock($token = 'token123')
    {
        $mockAuth = Mockery::mock(\Auth0\SDK\API\Authentication::class);
        $mockAuth->shouldReceive('userinfo')
            ->andReturn(
                MockHelper::userResponse()
            )->shouldReceive('dbconnections_change_password')
            ->andReturn(
                [
                    "Email sent"
                ]
            )->shouldReceive('client_credentials')
            ->andReturn(
                [
                    'access_token' => $token
                ]
            );

        return $mockAuth;
    }

    /**
     * Mock Management class from Auth0 Api
     *
     * @param array $data New user information
     *
     * @return Mock
     */
    static function managementMock($data = [])
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
            ->andReturn([
                'users' => [MockHelper::userResponse()],
                'total' => 1
            ]);
        return $mockManagement;
    }

    /**
     * Mock Authenticate Middleware
     *
     * @return Mock
     */
    static function authenticateMiddlewareMock($hasUser = true)
    {
        $user = $hasUser ? MockHelper::userResponse() : null;
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
        $user = $hasUser ? MockHelper::userResponse() : null;
        $mockAuth = Mockery::mock(App\Http\Services\AuthService::class);
        $mockAuth->shouldReceive('hasRoles')
            ->andReturn($hasRole)
            ->shouldReceive('isSuperUser')
            ->andReturn(true)
            ->shouldReceive('isProClubAdmin')
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
}
