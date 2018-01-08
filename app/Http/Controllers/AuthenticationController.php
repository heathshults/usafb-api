<?php

namespace App\Http\Controllers;

use Validator;
use DateInterval;
use App\Helpers\AuthHelper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
    protected $authService;

    /**
     * Initialize auth service
     *
     * @constructor
     */
    public function __construct()
    {
        $this->authService = app('Auth');
    }

    /**
     * Login user by email and password
     * Url: /login
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
        return $this->authService->login($request->input('email'), $request->input('password'));
    }

    /**
     * Refresh token
     * Url: POST /token
     *
     * @param Request $request
     *
     * @return json
     */
    public function refreshToken(Request $request)
    {
        $this->validate(
            $request,
            [
                'refresh_token' => 'required',
            ]
        );
        return $this->authService->refreshToken($request->input('refresh_token'));
    }

    /**
     * Get user information by token provided in header
     * Url: /me
     *
     * @param Request $request
     *
     * @return json
     */
    public function getAuthenticatedUser(Request $request)
    {
        //return $this->fractal->item($request->user(), $this->userTransformer);
        return $request->user();
    }

    /**
     * Send a confirmation code to reset password
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
        $this->authService->forgotPassword($request->input('email'));
        $result = [];        
        return $this->respond('OK', $result);
    }

    /**
     *
     * Url: /activate-user
     *
     * @param Request $request
     *
     * @return json
     */
    public function activateUser(Request $request)
    {
        $this->validate(
            $request,
            [
                'email' => 'required|email',
                'password' => [
                    'required',
                    'min:8',
                    'regex:/^((?=.*\\d)(?=.*[a-zA-Z])(?=.*[@#$%*!]))/'
                ],
                'session' =>'required'
            ]
        );
        $result = $this->authService->activateUser(
            $request->input('email'),
            $request->input('password'),
            $request->input('session')
        );
        return $this->respond('OK', $result);
    }

    /**
     * Change the password using a confirmation code
     * Url: /confirm-forgot-password
     *
     * @param Request $request
     *
     * @return json
     */
    public function confirmForgotPassword(Request $request)
    {
        $this->validate(
            $request,
            [
                'email' => 'required|email',
                'code' => 'required',
                'password' => [
                    'required',
                    'min:8',
                    'regex:/^((?=.*\\d)(?=.*[a-zA-Z])(?=.*[@#$%*!]))/'
                ]
            ]
        );
        $this->authService->confirmForgotPassword(
            $request->input('email'),
            $request->input('code'),
            $request->input('password')
        );
        $result = [];
        return $this->respond('OK', $result);
    }
}
