<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;

use App\Http\Services\AuthService;

use App\Transformers\UserTransformer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * UsersController
 * Manage users information
 *
 * @package    Http
 * @subpackage Controllers
 */
class UsersController extends Controller
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
     * Get users
     * Url: GET /users
     *
     * @param Request $request
     *
     * @return json
     */
    public function index(Request $request)
    {
        $sort = $this->buildSortCriteria($request->query(), [ 'column' => 'created_at', 'order' => 'desc' ]);
        $paginationCriteria = $this->buildPaginationCriteria($request->query());
        $users = User::orderBy($sort['column'], $sort['order'])->paginate($paginationCriteria['per_page']);
        return $this->respond('OK', $users);
    }

    /**
     * Get user
     * Url: GET /users/:id
     *
     * @param Request $request
     * @param string $id
     *
     * @return json
     */
    public function show(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'User ('.$id.') not found.']]);
        }
        return $this->respond('OK', $user);
    }

    /**
     * Create user
     * Url: POST /users
     *
     * @param Request $request
     *
     * @return json
     */
    public function create(Request $request)
    {
        $user = new User();
        $user->id_external = $request->input('id_external');
        $user->name_first = $request->input('name_first');
        $user->name_last = $request->input('name_last');
        $user->phone = $request->input('phone');
        $user->email = $request->input('email');
        $user->role_id = $request->input('role_id');
        $user->address = $request->input('address');
        
        // fail before setting up Cognito record if user invalid
        if (!$user->valid()) {
            $errors = $user->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error creating user record.',
                    'errors' => $errors
                ]
            ]);
        }
        
        // create record in Cognito, return Cognito ID if successful
        $idCognito = $this->authService->createUser($user->email);
        
        if (!is_null($idCognito)) {
            // set Cognito ID and save new user record
            $user->id_cognito = $idCognito;
            $user->save();
            return $this->respond('CREATED', $user);
        } else {
            // fail-safe in case an exception is not raised during failure
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error creating user record.'
                ]
            ]);
        }
    }

    /**
     * Delete user by id
     * Url: DELETE /users/{id}
     *
     * @param Request $request
     * @param Request $id
     *
     * @return json
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'User ('.$id.') not found.']]);
        }
        $user->delete();
        return $this->respond('OK', $user);
    }

    /**
     * Update user
     * Url: PUT /users/{id}
     *
     * @param Request $request
     * @param string $userId User id
     *
     * @return json
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'User ('.$id.') not found.']]);
        }
        
        if ($request->has('id_external')) {
            $user->id_external = $request->input('id_external');
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
        // Don't allow changing email since it's tied to user account in Cognito
        /*
        if ($request->has('email')) {
            $user->email = $request->input('email');
        }
        */
        if ($request->has('role_id')) {
            $user->role_id = $request->input('role_id');
        }
        if ($request->has('address')) {
            $user->address = $request->input('address');
        }
                
        if ($user->valid() && $user->save()) {
            return $this->respond('ACCEPTED', $user);
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
     * Activate user by id
     * Url: PUT /users/{id}/activate
     *
     * @param Request $request
     * @param Request $id
     *
     * @return json
     */
    public function activate(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'User ('.$id.') not found.']]);
        }
        if ($user->activate()) {
            return $this->respond('ACCEPTED', $user);
        } else {
            return $this->respond('NOT_MODIFIED', $user);
        }
    }
    
    /**
     * Deactivate user by id
     * Url: PUT /users/{id}/deactivate
     *
     * @param Request $request
     * @param Request $id
     *
     * @return json
     */
    public function deactivate(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'User ('.$id.') not found.']]);
        }
        if ($user->deactivate()) {
            return $this->respond('ACCEPTED', $user);
        } else {
            return $this->respond('NOT_MODIFIED', $user);
        }
    }
}
