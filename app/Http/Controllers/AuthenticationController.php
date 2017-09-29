<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use App\Transformers\UserTransformer;
use App\Transformers\MessageTransformer;

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

    protected $authService;
    protected $fractal;
    protected $userTransformer;
    protected $messageTransformer;

    /**
     * Initialize auth service
     *
     * @constructor
     */
    public function __construct(Manager $fractal)
    {
        $this->authService = app('Auth');
        $this->fractal = $fractal;
        $this->userTransformer = new UserTransformer();
        $this->messageTransformer = new MessageTransformer();
    }

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
        return $this->authService->login($request->input(self::INPUT_EMAIL), $request->input(self::INPUT_PWD));
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
        $user = new Item($request->user(), $this->userTransformer);
        return $this->fractal->createData($user)->toArray();
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
        $response = $this->authService->forgotPassword($request->input(self::INPUT_EMAIL));

        $user = new Item($response, $this->messageTransformer);
        return $this->fractal->createData($user)->toArray();
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
        return $this->authService->resetPassword($request->input(self::INPUT_EMAIL));
    }
}
