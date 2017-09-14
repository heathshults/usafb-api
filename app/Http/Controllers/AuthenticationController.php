<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

/**
 * AuthenticationController
 * Manage authentication and users information
 *
 * @package    Http
 * @subpackage Controllers
 * @author     Daylen Barban <daylen.barban@bluestarsports.com>
 */
class AuthenticationController extends Controller
{

    const INPUT_EMAIL = 'email';
    const INPUT_PWD = 'password';
    /**
     * Login user by email and password
     * Url: /auth/login
     *
     * @param Request $request
     *
     * @return json
     */
    public function login(Request $request)
    {
        $this->validate(
            $request,
            [
                self::INPUT_EMAIL => 'required|email',
                self::INPUT_PWD => 'required'
            ]
        );
        return app('Auth')->login($request->input(self::INPUT_EMAIL), $request->input(self::INPUT_PWD));
    }

    /**
     * Get user information by token provided in header
     * Url: /auth/user
     *
     * @param Request $request
     *
     * @return json
     */
    public function getAuthenticatedUser(Request $request)
    {
       // TODO Replace this with User Transformer (Fractal)
        $user = $request->user();
        $response = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'phone_number' => $user->getPhoneNumber(),
            'city' => $user->getCity(),
            'state' => $user->getState(),
            'postal_code' => $user->getPostalCode(),
            'roles' => $user->getRoles(),
            'email_verified' => (boolean) $user->getEmailVerified(),
            'picture' => $user->getPicture(),
            'status' => $user->getStatus(),
            'updated_at' => $user->getUpdatedAt(),
            'created_at' => $user->getCreatedAt(),
            'last_login' => $user->getLastLogin()
        ];

        if (!is_null($user->getNickname())) {
            $response['nickname'] = $user->getNickname();
        }

        if (!is_null($user->getUpdatedBy())) {
            $response['updated_by'] = $user->getUpdatedBy();
        }

        if (!is_null($user->getCreatedBy())) {
            $response['created_by'] = $user->getCreatedBy();
        }
        return $response;
    }

    /**
     * Send a reset password link via email
     * Url: /forgot-password
     *
     * @param Request $request
     *
     * @return json
     */
    public function forgotPassword(Request $request)
    {
        $this->validate(
            $request,
            [
                self::INPUT_EMAIL => 'required|email'
            ]
        );
        return app('Auth')->forgotPassword($request->input(self::INPUT_EMAIL));
    }

    /**
     * Send a reset password link via email
     * Url: /reset-password
     *
     * @param Request $request
     *
     * @return json
     */
    public function resetPassword(Request $request)
    {
        $this->validate(
            $request,
            [
                self::INPUT_EMAIL => 'required|email'
            ]
        );
        return app('Auth')->resetPassword($request->input(self::INPUT_EMAIL));
    }
}
