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
                'email' => 'required|email',
                'password' => 'required'
            ]
        );
        return app('Auth')->login($request->input('email'), $request->input('password'));
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
                'email' => 'required|email'
            ]
        );
        return app('Auth')->forgotPassword($request->input('email'));
    }
}
