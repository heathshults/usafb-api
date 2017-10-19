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
use App\Exceptions\InternalException;
use GuzzleHttp\Exception\ClientException;
use App\Models\Enums\Role;
use App\Helpers\AuthHelper;
use App\Models\User;
use Log;

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
    protected $management;
    protected $authentication;
    protected $verifier;

    const EXISTENT_USER_MSG = "The email address submitted already exists in the system.";
    const PERMISSION_DENIED_MSG = "Permission denied.";
    const USER_METADATA = "user_metadata";

    const RETRY_INTERVALS = [1, 4, 8, 16, 32];

    const RATE_LIMIT_CODE = 429;

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

        $this->verifier = new JWTVerifier([
            'supported_algs' => [getenv('AUTH_ALG')],
            'valid_audiences' => [getenv('AUTH_CLIENT_ID')],
            'authorized_iss' => [getenv('AUTH_ISS')]
        ]);
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
     * Get token from Authorization header
     *
     * @param array $requestHeaders
     *
     * @throws UnauthorizedHttpException if authorization header is not provided
     * @throws UnauthorizedHttpException if token type is invalid
     * @throws UnauthorizedHttpException if token is not provided
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
            throw new UnauthorizedHttpException('Authentication', 'Invalid email or password.');
        }
    }

    /**
     * Normalize user
     * Remove user metadata namespace and replace sub index by id
     *
     * @param array $user
     *
     * @return $user
     */
    public function normalizeUser($user)
    {
        $user[self::USER_METADATA] = $user[getenv('AUTH_METADATA')];
        unset($user[getenv('AUTH_METADATA')]);

        $user['id'] = $user['sub'];
        unset($user['sub']);

        return $user;
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
            return $this->authentication->dbconnections_change_password(
                $email,
                getenv('AUTH_CONNECTION')
            );
        }
        throw new NotFoundHttpException("User not found");
    }

    /**
     * Send email to reset password
     *
     * @param string $email
     *
     * @throws NotFoundHttpException when user with email provided does not exists
     * @return json
     */
    public function resetPassword($email)
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
     * Get authenticated user profile by token provided in header
     * Retry if a Too Many Request Exception is thrown
     *
     * @param array $requestHeaders
     *
     * @return User|null user if response is not null or null otherwise
     * @throws UnauthorizedHttpException if user could not be authenticated
     * @throws InternalException if all retries were attempted
     */
    public function authenticatedUser($requestHeaders)
    {
        $token = AuthHelper::getHeaderToken($requestHeaders);
        try {
            return new User(json_decode(json_encode($this->verifier->verifyAndDecode($token)), true));
        } catch (\Auth0\SDK\Exception\CoreException $e) {
            Log::error($e->getMessage());
            throw new UnauthorizedHttpException('Authentication', 'Invalid token.');
        }
    }

    /**
     * Determines if a user is U.S. Soccer Staff
     * This user will have full access to all api endpoints except deletion
     *
     * @param array $user
     *
     * @return boolean
     */
    public function isSuperUser($user)
    {
        return $this->hasRoles($user, [Role::SUPER_USER]);
    }

    /**
     * Determines if a user is Automation Test user
     * This user will have full access to all api endpoints
     *
     * @param array $user
     *
     * @return boolean
     */
    public function isTestUser($user)
    {
        return $this->hasRoles($user, [Role::TEST]);
    }

    /**
     * Determines if a user has role
     *
     * @param array $user
     * @param array $roles
     *
     * @return boolean
     */
    public function hasRoles($user, $roles)
    {
        $metadata = $user[self::USER_METADATA];
        if (isset($metadata["roles"])) {
            foreach ($metadata["roles"] as $roleName) {
                if (is_array($roles) && in_array($roleName, $roles)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Determines if a client exception refers to Too many request exception
     *
     * @param GuzzleHttp\Exception\ClientException $exception
     *
     * @return boolean
     */
    public static function isTooManyRequestsException(ClientException $exception)
    {
        return $exception->getResponse()->getStatusCode() === self::RATE_LIMIT_CODE;
    }

    /**
     * Create user
     *
     * @param array $newUser
     *
     * @return json
     */
    public function createUser($newUser)
    {
        try {
            $userCreated = $this->getManagement()->users->create($newUser);
            $this->authentication->dbconnections_change_password(
                $userCreated["email"],
                getenv('AUTH_CONNECTION')
            );
            return new User($userCreated);
        } catch (ClientException $e) {
            $message = $e->getMessage();
            if (strpos($message, 'The user already exists') !== false) {
                $message = self::EXISTENT_USER_MSG;
            }
            throw new ConflictHttpException($message);
        }
    }

    /**
     * Get user by id
     *
     * @param string $userId
     *
     * @return json
     */
    public function getUserById($userId)
    {
        $user = $this->getManagement()->users->get($userId);
        return new User($user);
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
        if (empty($id)) {
            throw new BadRequestHttpException("Invalid id");
        }
        // This is a fix to throw an error if the user doesn't exist
        // Because the user delete returns an empty response
        $this->getUserById($id);
        return $this->getManagement()->users->delete($id);
    }

    /**
     * Delete user by id
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

    /**
     * Get fields that belongs to user metadata
     *
     * @return array
     */
    protected function metadataFields()
    {
        return [
            'first_name',
            'last_name',
            'city',
            'phone_number',
            'state',
            'postal_code',
            'modified_by'
        ];
    }

    /**
     * Update user by id
     *
     * @param string $id User id
     * @param array $data User data to be updated
     *
     * @return json
     */
    public function updateUser($userId, $data)
    {
        if (isset($data['role'])) {
            throw new BadRequestHttpException("Role can not be changed.");
        }
        if (isset($data['password']) || isset($data['email'])) {
            $data['connection'] = getenv('AUTH_CONNECTION');
        }
        if (isset($data['email'])) {
            $data['client_id'] = getenv('AUTH_CLIENT_ID');
        }
        $metadata = [];
        foreach (AuthHelper::METADATA_FIELDS as $field) {
            if (isset($data[$field])) {
                $metadata[$field] = $data[$field];
                unset($data[$field]);
            }
        }
        if (!empty($metadata)) {
            $data['user_metadata'] = $metadata;
        }

        try {
            $updatedUserResponse = $this->getManagement()->users->update($userId, $data);
            return new User($updatedUserResponse);
        } catch (ClientException $e) {
            $message = $e->getMessage();
            if (strpos($message, 'The specified new email already exists') !== false) {
                $message = self::EXISTENT_USER_MSG;
                throw new ConflictHttpException($message);
            }
            throw $e;
        }
    }

    /**
     * Get all Users
     *
     * @param string $criteria
     *
     * @return json
     */
    public function getAllUsers($criteria)
    {
        $options = [
            "search_engine" => "v2",
            "include_totals" => true,
            'per_page' => 10,
            'page' => 0
        ];
        if (isset($criteria['page'])) {
            $options['page'] = $criteria['page'] - 1;
        }
        if (isset($criteria['per_page'])) {
            $options['per_page'] = $criteria['per_page'];
        }
        if (isset($criteria['sort'])) {
            $options['sort'] = AuthHelper::extractSortField($criteria['sort']);
        }

        if (isset($criteria['q'])) {
            $options['q'] = "(" . AuthHelper::createFilterQuery($criteria['q']) . ")";
        }

        $result = $this->getManagement()->users->getAll($options);
        $users = [
            "page" => $options['page'] + 1,
            "per_page" => $options['per_page'],
            "total" => 0,
            "data" => []
        ];
        if (!empty($result['users'])) {
            $users["total"] = $result['total'];
            $users["data"] = AuthHelper::getUserList($result['users']);
        }
        return $users;
    }

    /**
     * Convert an string to a valid sort expression for auth0 search query
     *
     * @param string $sortExpression
     *
     * @return string sort query
     */
    public function extractSortField($sortExpression)
    {
        $field = trim($sortExpression);
        $order = 1;
        $firstCharacter = $field[0];
        if ($firstCharacter == '-' || $firstCharacter == '+') {
            $field = substr($field, 1);
            $order = intval($firstCharacter.'1');
        }
        $metadata_fields = $this->metadataFields();
        if (in_array($field, $metadata_fields)) {
            $field = 'user_metadata.'.$field;
        }
        $sort = $field.':'.$order;
        return $sort;
    }

    /**
     * Create a valid filter expression for auth0 search query
     *
     * @param string $criteria
     *
     * @return string filter query
     */
    public function createFilterQuery($criteria)
    {
        $escapedCriteria = urlencode($criteria);
        $filterQuery = "user_metadata.first_name:*".$escapedCriteria."*";
        $filterQuery .= " OR user_metadata.last_name:*".$escapedCriteria."*";
        $filterQuery .= ' OR email:"*'.$criteria.'*"';
        $filterQuery .=" OR user_metadata.roles:*".$escapedCriteria."*";

        return $filterQuery;
    }

    /**
     * Links array for pagination that will be included in search response
     *
     * @param string $currentPage
     * @param string $pageSize
     * @param string $totalPages
     *
     * @return array links
     */
    public function createLinksPagination($currentPage, $pageSize, $totalPages)
    {
        $usersUrl = getenv('HOSTNAME').'/users';
        $pageNumberParam = "?page[number]=";
        $pageSizeParam = "&page[size]=";

        $prev = null;
        if ($currentPage !== 0) {
            $prev = $usersUrl.$pageNumberParam.($currentPage - 1).$pageSizeParam.$pageSize;
        }
        $next = null;
        $lastPage = $totalPages - 1;
        if ($currentPage !== $lastPage) {
            $next = $usersUrl.$pageNumberParam.($currentPage + 1).$pageSizeParam.$pageSize;
        }
        return [
            "self" => $usersUrl.$pageNumberParam.$currentPage.$pageSizeParam.$pageSize,
            "first" => $usersUrl.$pageNumberParam."0".$pageSizeParam.$pageSize,
            "prev" => $prev,
            "next" =>  $next,
            "last" => $usersUrl.$pageNumberParam.$lastPage.$pageSizeParam.$pageSize
        ];
    }

    /**
     * Set TokenVerifier
     *
     * @param Auth0\SDK\JWTVerifier $verifier
     *
     * @return void
     */
    public function setVerifier($verifier)
    {
        $this->verifier =  $verifier;
    }
}
