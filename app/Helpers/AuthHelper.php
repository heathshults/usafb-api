<?php

namespace App\Helpers;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use App\Helpers\Constants;
use App\Models\User;
use Hashids\Hashids;

class AuthHelper
{
    const RESERVED_CHARACTERS = [
        '+', '-', '=', '&&', '||', '>',
        '<', '!', '(', ')', '{', '}', '[',
        ']', '^', '"', '~', '*', '?', ':', '/'
    ];

    const METADATA_FIELDS = [
        'first_name',
        'last_name',
        'city',
        'phone_number',
        'state',
        'postal_code',
        'country',
        'organization',
        'updated_by'
    ];

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
    public static function getHeaderToken($requestHeaders)
    {
        $requestHeaders = array_change_key_case($requestHeaders, CASE_LOWER);

        $authorizationHeader = isset($requestHeaders['authorization']) ?
            $requestHeaders['authorization'] :
            null;
        if (is_null($authorizationHeader)) {
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
     * Determines if a user has role
     *
     * @param User $user
     * @param array $roles
     *
     * @return boolean
     */
    public static function hasRoles($user, array $roles)
    {
        foreach ($user->getRoles() as $roleName) {
            if (in_array($roleName, $roles)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Convert an string to a valid sort expression for auth0 search query
     *
     * @param string $sortExpression
     *
     * @return string sort query
     */
    public static function extractSortField($sortExpression)
    {
        $field = trim($sortExpression);
        $order = 1;
        $firstCharacter = $field[0];
        if ($firstCharacter == '-' || $firstCharacter == '+') {
            $field = substr($field, 1);
            $order = intval($firstCharacter.'1');
        }
        if (in_array($field, AuthHelper::METADATA_FIELDS)) {
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
    public static function createFilterQuery($criteria)
    {
        /**
        * Auth0 reserved characters
        * Refers to https://auth0.com/docs/api/management/v2/query-string-syntax#reserved-characters
        */
        $scapedCharacters = self::RESERVED_CHARACTERS;
        array_walk(
            $scapedCharacters,
            function (&$value) {
                $value='\\'.$value;
            }
        );
        $specialCharsCriteria = str_ireplace(self::RESERVED_CHARACTERS, $scapedCharacters, $criteria);
        $escapedCriteria = str_ireplace(' ', '%20', $specialCharsCriteria);

        /* Except email, user field values blank spaces must be scaped */
        $filterQuery = "user_metadata.first_name:*".$escapedCriteria."*";
        $filterQuery .= " OR user_metadata.last_name:*".$escapedCriteria."*";
        $filterQuery .= ' OR email:"*'.$criteria.'*"';
        $filterQuery .=" OR user_metadata.roles:*".$escapedCriteria."*";

        return $filterQuery;
    }

    /**
     * Determines if a user is U.S. Soccer Staff
     * This user will have full access to all api endpoints except deletion
     *
     * @param User $user
     *
     * @return boolean
     */
    public static function isSuperUser($user)
    {
        return AuthHelper::hasRoles($user, [Role::SUPER_USER]);
    }

    /**
     * Determines if a user is Automation Test user
     * This user will have full access to all api endpoints
     *
     * @param User $user
     *
     * @return boolean
     */
    public static function isTestUser($user)
    {
        return AuthHelper::hasRoles($user, [Role::TEST]);
    }

    /**
     * Convert list of auth0 user response in a User list
     *
     * @param array $users array of auth0 user
     *
     * @return array User array
     */
    public static function getUserList($users)
    {
        return array_map(
            function ($user) {
                return new User($user);
            },
            $users
        );
    }

    /**
     * Returns corresponding property name of response base on user property
     *
     * @return string property name
     */
    public static function getPropertyLabel($property)
    {
        return ucfirst(strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1 $2", $property)));
    }

    /**
     * Generate a hash from an id
     *
     * @param int $id
     * @param number $length Hash length
     *
     * @return string id hash
     */
    public static function generateId($id, $length = 30)
    {
        /**
        * Random project name to make unique ids.
        * For the same id a new hash is generated everytime
        */
        $now = \DateTime::createFromFormat('U.u', microtime(true));
        $projectName = $now->format("m-d-Y H:i:s.u");

        $hashids = new Hashids($projectName, $length);
        return $hashids->encode($id);
    }
}
