<?php

namespace App\Helpers;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use App\Helpers\Constants;
use App\Models\User;
use Hashids\Hashids;

class AuthHelper
{

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
        $now = new \DateTime();
        $projectName = $now->format("m-d-Y H:i:s.u");

        $hashids = new Hashids($projectName, $length);
        return $hashids->encode($id);
    }
}
