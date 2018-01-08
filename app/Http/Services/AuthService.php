<?php

namespace App\Http\Services;

use App\Exceptions\InternalException;
use App\Exceptions\ExpiredTokenException;
use App\Helpers\AuthHelper;
use App\Models\User;

use Aws\Credentials\Credentials;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;

use Carbon\Carbon;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * AuthService
 * Access to Auth api for authentication and user management
 *
 * @package    Http
 * @subpackage Services
 * @author     Daylen Barban <daylen.barban@bluestarsports.com>
 */
class AuthService
{
    protected $client;

    const EXISTENT_USER_MSG = 'The email address submitted already exists in the system.';
    const PERMISSION_DENIED_MSG = 'Permission denied.';
    const ADMIN_AUTH_FLOW = 'ADMIN_NO_SRP_AUTH';
    const REFRESH_TOKEN_AUTH = 'REFRESH_TOKEN_AUTH';
    const NEW_PSWD_REQUIRED = 'NEW_PASSWORD_REQUIRED';
    const PSWD_REQ_CODE = 202;
    const ACCOUNT_DEACTIVATED = 'This account is no longer active. ' .
        'If you feel you have received this in error, please contact U.S.A. Football.';

    /**
     * Initialize authentication client with auth credentials
     *
     * @constructor
     */
    public function __construct()
    {
        $this->client = new CognitoIdentityProviderClient(config('aws'));
    }

    /**
     * Login user by email and password
     *
     * @param string $username
     * @param string $password
     *
     * @throws UnauthorizedHttpException if email or password is invalid
     * @return json
     */
    public function login($username, $password)
    {
        $method = __METHOD__;
        try {
            $user = User::where('email', $username)->firstOrFail();
            
            if (!$user->active) {
                throw new UnauthorizedHttpException('Deactivation', self::ACCOUNT_DEACTIVATED);
            }
            
            $result = $this->client->adminInitiateAuth([
                'AuthFlow' => self::ADMIN_AUTH_FLOW,
                'ClientId' => env('AWS_COGNITO_CLIENT_ID'),
                'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID'),
                'AuthParameters' => [
                    'USERNAME' => $username,
                    'PASSWORD' => $password,
                ],
            ]);
            
            if ($result->get('ChallengeName') == self::NEW_PSWD_REQUIRED) {
                return response()->json(
                    [
                        'session' => $result->get('Session'),
                        'challenge' => $result->get('ChallengeName'),
                    ],
                    self::PSWD_REQ_CODE
                );
            }
            
            $response = $result->get('AuthenticationResult');

            $user->update(['last_login_at' => Carbon::now()->toDateTimeString()]);

            return [
                'id_token' => $response['IdToken'],
                'access_token' => $response['AccessToken'],
                'expires_in' => $response['ExpiresIn'],
                'token_type' => $response['TokenType'],
                'refresh_token' => $response['RefreshToken']
            ];
        } catch (CognitoIdentityProviderException $e) {
            throw new UnauthorizedHttpException('Authentication', 'Invalid email or password.');
        }
    }

    /**
     * Login user by email and password
     *
     * @param string $username
     * @param string $password
     *
     * @throws UnauthorizedHttpException if email or password is invalid
     * @return json
     */
    public function refreshToken($token)
    {
        $method = __METHOD__;
        try {
            $result = $this->client->adminInitiateAuth([
                'AuthFlow' => self::REFRESH_TOKEN_AUTH,
                'ClientId' => env('AWS_COGNITO_CLIENT_ID'),
                'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID'),
                'AuthParameters' => [
                    'REFRESH_TOKEN' => $token,
                ],
            ]);
            if ($result->get('ChallengeName') == self::NEW_PSWD_REQUIRED) {
                return response()->json(
                    [
                        'session' => $result->get('Session'),
                        'challenge' => $result->get('ChallengeName')
                    ],
                    self::PSWD_REQ_CODE
                );
            }
            $response = $result->get('AuthenticationResult');

            return [
                'id_token' => $response['IdToken'],
                'access_token' => $response['AccessToken'],
                'expires_in' => $response['ExpiresIn'],
                'token_type' => $response['TokenType'],
            ];
        } catch (CognitoIdentityProviderException $e) {
            throw new UnauthorizedHttpException('Authentication', 'Unable to refresh token.');
        }
    }

    /**
     * Get authenticated user profile by token provided in header
     * Retry if a Too Many Request Exception is thrown
     *
     * @param array $requestHeaders
     *
     * @return User|null                 user if response is not null or null otherwise
     * @throws UnauthorizedHttpException if user could not be authenticated
     * @throws InternalException         if all retries were attempted
     */
    public function authenticatedUser($requestHeaders)
    {
        $method = __METHOD__;
        $token = AuthHelper::getHeaderToken($requestHeaders);
        try {
            $userId = Cache::get('user::' . $token);
            if (!isset($userId)) {
                $cognitoUser = $this->client->getUser([
                    'AccessToken' => $token,
                ]);
                $userAttributes = $cognitoUser->get('UserAttributes');
                foreach ($userAttributes as $field) {
                    if ($field['Name'] == 'sub') {
                        $userId = $field['Value'];
                        break;
                    }
                }                
                Cache::put('user::' . $token, $userId, 60);
            }            
            $userProfile = User::where('id_cognito', $userId)->first();
            if (is_null($userProfile)) {
                throw new UnauthorizedHttpException('Authentication', 'Invalid token.');
            }
            return $userProfile;
        } catch (CognitoIdentityProviderException $e) {
            $errorMessage = $e->getAwsErrorMessage();
            if (!is_null($errorMessage) && $errorMessage == 'Access Token has expired') {
                throw new ExpiredTokenException('Authentication', $errorMessage);                
            } else {
                throw new UnauthorizedHttpException('Authentication', $errorMessage);                    
            }
        }
    }

    /**
     * Activates user after new password is set
     *
     * @param array $requestHeaders
     *
     * @return User|null                 user if response is not null or null otherwise
     * @throws UnauthorizedHttpException if user could not be authenticated
     * @throws InternalException         if all retries were attempted
     */
    public function activateUser($email, $password, $session)
    {
        $method = __METHOD__;
        try {
            $user = User::where('email', $email)->firstOrFail();
            $result = $this->client->adminRespondToAuthChallenge(
                [
                'ChallengeName' => self::NEW_PSWD_REQUIRED,
                'ChallengeResponses' => [
                    'NEW_PASSWORD' => $password,
                    'USERNAME' => $email,
                ],
                'ClientId' => env('AWS_COGNITO_CLIENT_ID'),
                'Session' => $session,
                'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID'),
                ]
            );
            $response = [];
            if (!is_null($result->get('AuthenticationResult'))) {
                $this->client->adminUpdateUserAttributes([
                    'UserAttributes' => [
                        [
                            'Name' => 'email_verified',
                            'Value' => 'true',
                        ],
                    ],
                    'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID'),
                    'Username' => $email,
                ]);
                $result = $result->get('AuthenticationResult');
                $response = [
                    'id_token' => $result['IdToken'],
                    'access_token' => $result['AccessToken'],
                    'expires_in' => $result['ExpiresIn'],
                    'token_type' => $result['TokenType'],
                ];
            }
            return $response;
        } catch (CognitoIdentityProviderException $e) {
            throw new BadRequestHttpException('Invalid session or email.');
        }
    }

    /**
     * Activates user after new password is set
     *
     * @param userId
     * @param email
     *
     * @return boolean                   if successful
     * @throws UnauthorizedHttpException if user could not be authenticated
     * @throws InternalException         if all retries were attempted
     */
    public function updateUser($userId, $email)
    {
        $method = __METHOD__;
        try {
            $user = User::findOrFail($userId);
            $this->client->adminUpdateUserAttributes([
                'UserAttributes' => [
                    [
                        'Name' => 'email_verified',
                        'Value' => 'true',
                    ],
                    [
                        'Name' => 'email',
                        'Value' => $email,
                    ],
                ],
                'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID'),
                'Username' => $user->email,
            ]);
            $user->email = $email;
            $user->save();
            return $user;
        } catch (CognitoIdentityProviderException $e) {
            throw new BadRequestHttpException('Unable to update user email.');
        }
    }

    /**
     * Create user
     *
     * @param $email
     *
     * @throws ConflictHttpException if user already exists
     * @return json
     */
    public function createUser($email)
    {
        try {
            $user = User::where('email', $email)->first();
            if (!is_null($user)) {
                throw new ConflictHttpException(self::EXISTENT_USER_MSG);
            }
            $result = $this->client->adminCreateUser([
                'DesiredDeliveryMediums' => ['EMAIL'],
                'TemporaryPassword' => bin2hex(random_bytes(8)) . 'Aa1*',
                'UserAttributes' => [
                    [
                        'Name' => 'email',
                        'Value' => $email,
                    ],
                ],
                'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID'),
                'Username' => $email,
            ]);

            $cognitoUser = $result->get('User');
            $userId = $cognitoUser['Username'];
            return $userId;
        } catch (CognitoIdentityProviderException $e) {
            throw new BadRequestHttpException($e->getAwsErrorMessage());
        }
    }

    /**
     * Delete user by id
     *
     * @param string $email
     *
     * @throws BadRequestHttpException if problem occurs deleting user
     * @return json
     */
    public function deleteUser($email)
    {
        try {
            $result = $this->client->adminDeleteUser([
                'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID'),
                'Username' => $email,
            ]);
            return true;
        } catch (CognitoIdentityProviderException $e) {
            throw new BadRequestHttpException($e->getAwsErrorMessage());
        }
    }

    /**
     * Change password of a user
     *
     * @param array  $requestHeaders
     * @param string $previousPswd
     * @param string $newPswd
     *
     * @throws BadRequestHttpException if any exception is thrown
     * @return json
     */
    public function resetPassword($requestHeaders, $previousPswd, $newPswd)
    {
        $method = __METHOD__;
        $token = AuthHelper::getHeaderToken($requestHeaders);
        try {
            return $this->client->changePassword(
                [
                    'AccessToken' => $token,
                    'PreviousPassword' => $previousPswd,
                    'ProposedPassword' => $newPswd,
                ]
            );
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    /**
     * Change password of a user
     *
     * @param array  $requestHeaders
     * @param string $previousPswd
     * @param string $newPswd
     *
     * @throws BadRequestHttpException if any exception is thrown
     * @return json
     */
    public function forgotPassword($email)
    {
        $method = __METHOD__;
        try {
            $user = User::where('email', $email)->firstOrFail();
            $result = $this->client->forgotPassword(
                [
                    'ClientId' => env('AWS_COGNITO_CLIENT_ID'),
                    'Username' => $email,
                ]
            );
            return $result;
        } catch (CognitoIdentityProviderException $e) {
            throw new BadRequestHttpException($e->getAwsErrorMessage());
        }
    }

    /**
     * Confirm Forgot password
     *
     * @param string $email
     * @param string $confirmationCode
     * @param string $password
     *
     * @throws BadRequestHttpException if CognitoIdentityProviderException is thrown
     */
    public function confirmForgotPassword($email, $confirmationCode, $password)
    {
        $method = __METHOD__;
        try {
            $user = User::where('email', $email)->firstOrFail();
            $this->client->confirmForgotPassword([
                'ClientId' => env('AWS_COGNITO_CLIENT_ID'),
                'ConfirmationCode' => $confirmationCode,
                'Password' => $password,
                'Username' => $email,
            ]);
        } catch (CognitoIdentityProviderException $e) {
            throw new BadRequestHttpException('Invalid Data.');
        }
    }

    public function setClient($client)
    {
        $this->client = $client;
    }
}
