<?php

namespace App\Http\Services;

use Auth0\SDK\JWTVerifier;
use Auth0\SDK\API\Management;
use Auth0\SDK\API\Authentication;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
    protected $token;
    protected $tokenInfo;
    protected $domain = 'daylen.auth0.com';
    protected $management;
    protected $authentication;

    /**
     * Initialize authentication client with auth credentials
     *
     * @constructor
     */
    public function __construct()
    {
        $this->authentication = new Authentication(
            $this->domain,
            getenv('AUTH_CLIENT_ID'),
            getenv('AUTH_CLIENT_SECRET'),
            getenv('AUTH_AUDIENCE')
        );
    }

    /**
     * Verify and decode a token and initialize management client
     *
     * @param String $token
     *
     * @return void
     */
    public function setCurrentToken($token)
    {
        try {
            $verifier = new JWTVerifier(
                [
                    'supported_algs' => ['RS256'],
                    'valid_audiences' => 'https://daylen.auth0.com/api/v2/',
                    'authorized_iss' => 'https://daylen.auth0.com/'
                ]
            );
            $this->token = $token;
            $this->tokenInfo = $verifier->verifyAndDecode($this->token);
            $this->management = new Management($this->token, $this->domain);
        } catch (\Auth0\SDK\Exception\CoreException $e) {
            throw $e;
        }
    }

    /**
     * Verify user is authenticated by token provided in header
     *
     * @param array $requestHeaders
     *
     * @return boolean
     */
    public function authenticate($requestHeaders)
    {
        try {
            $token = $this->getHeaderToken($requestHeaders);
            $this->setCurrentToken($token);
            return true;
        } catch (\Auth0\SDK\Exception\CoreException $e) {
            throw $e;
        }
    }

    /**
     * Get token from Authorization header
     *
     * @param array $requestHeaders
     *
     * @return string
     */
    public function getHeaderToken($requestHeaders)
    {
        $authorizationHeader = isset($requestHeaders['authorization']) ?
            $requestHeaders['authorization'] :
            (isset($requestHeaders['Authorization']) ?
            $requestHeaders['Authorization'] : null);

        if ($authorizationHeader == null) {
            throw new UnauthorizedHttpException('Header', 'No authorization header provided');
        }

        $authorizationHeader = str_replace('bearer ', '', $authorizationHeader);
        $token = str_replace('Bearer ', '', $authorizationHeader);
        return $token[0];
    }

    /**
     * Login user by email and password
     *
     * @param string $username
     * @param string $password
     *
     * @return json
     */
    public function login($username, $password)
    {
        return $this->authentication->login(
            array(
                'username' => $username,
                'password' => $password,
                'realm' => 'Username-Password-Authentication'
            )
        );
    }

    /**
     * Get user profile by token provided in header
     *
     * @param array $requestHeaders
     *
     * @return json
     */
    public function getUser($requestHeaders)
    {
        $token = $this->getHeaderToken($requestHeaders);
        return $this->authentication->userinfo($token);
    }

    /**
     * Set authentication client
     *
     * @param Auth0\SDK\API\Authentication $authentication
     *
     * @return void
     */
    public function setAuthentication($authentication)
    {
        $this->authentication =  $authentication;
    }
}
