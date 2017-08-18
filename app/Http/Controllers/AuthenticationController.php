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
    public function getUser(Request $request)
    {
        $headers = $request->headers->all();
        return app('Auth')->authenticatedUser($headers);
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
}
