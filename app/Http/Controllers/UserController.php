<?php

namespace App\Http\Controllers;

use App\Exceptions\ResetPasswordException;
use App\Http\Services\AuthService;
use App\Models\Import;
use App\Models\User;
use App\Models\Role;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;

/**
 * UserController
 * Manage own/callers user information
 *
 * @package    Http
 * @subpackage Controllers
 */
class UserController extends Controller
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
     * Get user's imports
     * Url: GET /user/imports
     *
     * @param Request $request
     *
     * @return json
     */
    public function imports(Request $request)
    {
        $user = $request->user();
        if (is_null($user)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'User not found.']]);
        }
        $sort = $this->buildSortCriteria($request->query(), [ 'column' => 'created_at', 'order' => 'desc' ]);
        $paginationCriteria = $this->buildPaginationCriteria($request->query());
        $imports= $user->imports()->orderBy($sort['column'], $sort['order'])->paginate($paginationCriteria['per_page']);
        return $this->respond('OK', $imports);
    }

    /**
     * Get user
     * Url: GET /user
     *
     * @param Request $request
     *
     * @return json
     */
    public function show(Request $request)
    {
        $user = $request->user();
        if (is_null($user)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'User not found.']]);
        }
        $userTransformer = new UserTransformer();
        $transformedUser = $userTransformer->transform($user);
        return $this->respond('ACCEPTED', $transformedUser);
    }

    /**
     * Update user
     * Url: PUT /user
     *
     * @param Request $request
     *
     * @return json
     */
    public function update(Request $request)
    {
        $user = $request->user();
        if (is_null($user)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'User ('.$id.') not found.']]);
        }
        if ($request->has('name_first')) {
            $user->name_first = $request->input('name_first');
        }
        if ($request->has('name_last')) {
            $user->name_last = $request->input('name_last');
        }
        if ($request->has('phone')) {
            $user->phone = $request->input('phone');
        }
        if ($request->has('address')) {
            $user->address = $request->input('address');
        }
        if ($user->valid() && $user->save()) {
            $userTransformer = new UserTransformer();
            $transformedUser = $userTransformer->transform($user);
            return $this->respond('ACCEPTED', $transformedUser);
        } else {
            $errors = $user->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error updating user record.',
                    'errors' => $user->errors()
                ]
            ]);
        }
    }
    
    /**
     * Update user password
     * Url: PUT /user/password
     *
     * @param Request $request
     *
     * @return json
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();
        
        if (is_null($user)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'User ('.$id.') not found.']]);
        }
        
        // validate current and new password before submitting to authService
        
        $validationRules = [
            'password_current' => [
                'required',
                'min:8',
            ],
            'password_new' => [
                'required',
                'min:8',
                'regex:/^((?=.*\\d)(?=.*[a-zA-Z])(?=.*[@#$%*!]))/'
            ],
        ];
                
        $passwordCurrent = $request->input('password_current');
        $passwordNew = $request->input('password_new');
                
        $validationValues = [
            'password_current' => $passwordCurrent,
            'password_new' => $passwordNew,
        ];
        
        $validator = Validator::make($validationValues, $validationRules);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error updating password.',
                    'errors' => $errors
                ]
            ]);
        }
        
        try {
            $resetResponse = $this->authService->resetPassword(
                $request->headers->all(),
                $passwordCurrent,
                $passwordNew
            );
        } catch (ResetPasswordException $resetEx) {
            return $this->respond('INVALID', [
                'error' => [ 'message' => $resetEx->getMessage() ]
            ]);
        }
        
        response()->json(['data' => 'OK']);
    }
}
