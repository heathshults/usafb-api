<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Mail;
use Auth0\SDK\JWTVerifier;
use Auth0\SDK\API\Management;
use Auth0\SDK\API\Authentication;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use GuzzleHttp\Exception\ClientException;
use App\Models\Enums\Role;

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
            getenv('AUTH_DOMAIN'),
            getenv('AUTH_CLIENT_ID'),
            getenv('AUTH_CLIENT_SECRET'),
            getenv('AUTH_AUDIENCE')
        );
    }

    public function getAccessTokenClient()
    {
        $authorization = $this->authentication->client_credentials(
            [
                'client_id' => getenv('AUTH_CLIENT_ID'),
                'client_secret' => getenv('AUTH_CLIENT_SECRET'),
                'audience' => getenv('AUTH_AUDIENCE')
            ]
        );
        return isset($authorization['access_token']) ? $authorization['access_token'] : null;
    }

    public function getManagement()
    {
        if ($this->management === null) {
            $accessToken = $this->getAccessTokenClient();
            $this->management = new Management($accessToken, getenv('AUTH_DOMAIN'));
        }
        return $this->management;
    }

    /**
     * Verify user is authenticated by token provided in header
     *
     * @param array $requestHeaders
     *
     * @return void
     */
    public function authenticate($requestHeaders)
    {
        $this->token = $this->getHeaderToken($requestHeaders);
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
        $requestHeaders = array_change_key_case($requestHeaders, CASE_LOWER);

        $authorizationHeader = isset($requestHeaders['authorization']) ?
            $requestHeaders['authorization'] :
            null;
        if ($authorizationHeader == null) {
            throw new UnauthorizedHttpException('Authorization', 'No authorization header provided.');
        }
        $tokenWithType = $authorizationHeader[0];

        $tokenType = 'Bearer';
        if (!(0 === stripos($tokenWithType, $tokenType))) {
            throw new UnauthorizedHttpException($tokenType, 'Invalid token type.');
        }

        $token = trim(substr($tokenWithType, strlen($tokenType)));

        if (empty($token)) {
            throw new UnauthorizedHttpException($tokenType, 'No token provided.');
        }
        return $token;
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
        try {
            return $this->authentication->login(
                array(
                    'username' => $username,
                    'password' => $password,
                    'realm' => getenv('AUTH_CONNECTION')
                )
            );
        } catch (ClientException $e) {
            throw new UnauthorizedHttpException('Authentication', 'Wrong email or password.');
        }
    }

    /**
     * Get user profile by token provided in header
     *
     * @param array $requestHeaders
     *
     * @return json
     */
    public function getUser()
    {
        try {
            return $this->authentication->userinfo($this->token);
        } catch (ClientException $e) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid token.');
        }
    }

    /**
     * Determines if a user is U.S. Soccer Staff
     * This user will have full access to all api endpoints except deletion
     *
     * @return boolean
     */
    public function isSuperUser($user)
    {
        $metadata = $user[getenv('AUTH_METADATA')];
        if (isset($metadata["roles"])) {
            foreach ($metadata["roles"] as $roleId) {
                if ($roleId == Role::USSF_ADMIN) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Determines if a user is Automation Test user
     * This user will have full access to all api endpoints
     *
     * @return boolean
     */
    public function isTestUser($user)
    {
        $metadata = $user[getenv('AUTH_METADATA')];
        if (isset($metadata["roles"])) {
            foreach ($metadata["roles"] as $roleId) {
                if ($roleId == Role::TEST) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Create user
     *
     * @param array $user
     *
     * @return json
     */
    public function createUser($newUser)
    {

        $user = $this->getUser();
        if ($this->isSuperUser($user)) {
            try {
                $userCreated = $this->getManagement()->users->create($newUser);
                $this->authentication->dbconnections_change_password(
                    $userCreated["email"],
                    getenv('AUTH_CONNECTION')
                );
                return $userCreated;
            } catch (ClientException $e) {
                $message = $e->getMessage();
                if (strpos($message, 'The user already exists') !== false) {
                    $message = "The email address submitted already exists in the system.";
                }
                throw new ConflictHttpException($message);
            }
        } else {
            throw new AccessDeniedHttpException("Permission denied.");
        }
    }

    /**
     * Change Password by user token
     *
     * @param integer $id
     *
     * @return json
     */
    public function changePassword($id, $password)
    {
        $user = $this->getUser();
        if ($this->isSuperUser($user)) {
            $data = [
                "password" => $password
            ];
            return $this->updateUser($id, $data);
        } else {
            throw new AccessDeniedHttpException("Permission denied.");
        }
    }

    /**
     * Get user by id
     *
     * @param string $id
     *
     * @return json
     */
    public function getUserById($id)
    {
        $user = $this->getUser();
        if ($this->isSuperUser($user)) {
            return $this->getManagement()->users->get($id);
        } else {
            throw new AccessDeniedHttpException("Permission denied.");
        }
    }

    /**
     * Get all Users
     *
     * @param string $token
     *
     * @return json
     */
    public function getAllUsers()
    {
        $user = $this->getUser();
        if ($this->isSuperUser($user)) {
            $users = $this->getManagement()->users->getAll([]);
            return $users;
        } else {
            throw new AccessDeniedHttpException("Permission denied.");
        }
    }

    /**
     * Delete user by id
     *
     * @param integer $id
     *
     * @return json
     */
    public function deleteUser($id)
    {


        $user = $this->getUser();
        if ($this->isTestUser($user)) {
            if (empty($id)) {
                throw new BadRequestHttpException("Invalid id");
            }
            $userToDelete = $this->getUserById($id);
            return $this->getManagement()->users->delete($id);
        } else {
            throw new AccessDeniedHttpException("Permission denied");
        }
    }

    /**
     * Get roles
     *
     * @return json
     */
    public function getRoles()
    {

        $user = $this->getUser();
        if ($this->isSuperUser($user)) {
            return response()->json(Role::labels());
        } else {
            throw new AccessDeniedHttpException("Permission denied");
        }
    }

    /**
     * Send email to reset password
     *
     * @param string $email
     *
     * @return json
     */
    public function forgotPassword($email)
    {
        $user = $this->getUserByEmail($email);
        if ($user !== null) {
            $emailSentMessage = $this->authentication->dbconnections_change_password(
                $email,
                getenv('AUTH_CONNECTION')
            );
            return response()->json(["message" => $emailSentMessage]);
        }
        throw new NotFoundHttpException("User not found");
    }

    /**
     * Get user by criteria
     *
     * @param string $email
     *
     * @return json
     */
    public function getUserByEmail($email)
    {
        $options = [
            'search_engine' => 'v2',
            'q' => "email.raw:".$email
        ];
        $users = $this->getManagement()->users->getAll($options);
        return empty($users) ? null : $users[0];
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

    /**
     * Set Management client
     *
     * @param Auth0\SDK\API\Management $management
     *
     * @return void
     */
    public function setManagement($management)
    {
        $this->management =  $management;
    }
}
