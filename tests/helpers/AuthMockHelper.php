<?php

namespace Tests\Helpers;

use Mockery;
use App\Models\Enums\Role;
use App\Models\User;
use App\Transformers\UserTransformer;

class AuthMockHelper
{
    /**
     * User profile returned by auth
     *
     * @return array
     */
    static function authUserResponse()
    {
        return [
            'sub' => 'auth0|123',
            'name' => 'Jhon Smith',
            'email' => 'smith@gmail.com',
            'connection' => 'connection',
            getenv('AUTH_METADATA') => [
                'first_name' => 'Jhon',
                'last_name' => 'Smith',
                'city' => '',
                'phone_number' => '',
                'state' => '',
                'country' => '',
                'postal_code' => '',
                'roles' => [Role::SUPER_USER],
                'created_by' => 'auth0|456',
                'updated_by' => 'auth0|789'
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
            'created_at'=> '2017-07-24T20:45:26.793Z',
            'last_login'=> '2017-07-24T20:45:26.793Z'
        ];
    }

    /**
     * Normalized user response
     *
     * @return array
     */
    static function userResponse()
    {
        $userTransformer = new UserTransformer();
        return $userTransformer->transform(AuthMockHelper::user());
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
            return new User(array_merge(AuthMockHelper::authUserResponse(), $data));
        }

        return new User($userResponse);
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
            'cuntry'=> '',
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
                ->andReturn(AuthMockHelper::authUserResponse());
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

        $userUpdated = array_merge(AuthMockHelper::authUserResponse(), $data);
        $mockManagement = Mockery::mock(\Auth0\SDK\API\Management::class);
        $mockManagement->users = Mockery::mock(\Auth0\SDK\API\Management\Users::class);
        $mockManagement->users->shouldReceive('update')
            ->andReturn(
                $userUpdated
            )
            ->shouldReceive('create')
            ->andReturn(AuthMockHelper::authUserResponse())
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
        $user = $hasUser ? AuthMockHelper::user() : null;
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
        $user = $hasUser ? AuthMockHelper::user() : null;
        $mockAuth = Mockery::mock(App\Http\Services\AuthService::class);
        $mockAuth->shouldReceive('hasRoles')
            ->andReturn($hasRole)
            ->shouldReceive('isSuperUser')
            ->andReturn(true)
            ->shouldReceive('isAdmin')
            ->andReturn(false)
            ->shouldReceive('authenticatedUser')
            ->andReturn($user)
            ->shouldReceive('getAllUsers')
            ->andReturn(
                [
                    'data' => [$user, $user, $user],
                    'total' => 3,
                    'per_page' => 1,
                    'page' => 1
                ]
            )
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
     * Mock filter query response
     *
     * @return string
     */
    static function filterQueryMock($criteria, $scapedCriteria)
    {
        return "user_metadata.first_name:*".$scapedCriteria."*"
        ." OR user_metadata.last_name:*".$scapedCriteria."*"
        .' OR email:"*'.$criteria.'*"'
        ." OR user_metadata.roles:*".$scapedCriteria."*";
    }

    /**
     * Mock TokenVerifier
     *
     * @return Mock
     */
    static function tokenVerifierMock($exception = null)
    {
        $mockVerifier = Mockery::mock(\Auth0\SDK\JWTVerifier::class);
        if (is_null($exception)) {
            $mockVerifier->shouldReceive('verifyAndDecode')
                ->with('token123')
                ->andReturn((object)AuthMockHelper::authUserResponse());
        } else {
            $mockVerifier->shouldReceive('verifyAndDecode')
                ->with('token123')
                ->andThrow($exception);
        }
        return $mockVerifier;
    }

    /**
     * Mock Auth0 Core Exception
     *
     * @return Mock
     */
    static function coreExceptionMock()
    {
        return Mockery::mock(\Auth0\SDK\Exception\CoreException::class);
    }


}
