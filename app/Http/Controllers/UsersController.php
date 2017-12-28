<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
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
        
        $data = $request->json()->all();
        $user->id_external = $request->input('id_external');
        $user->name_first = $request->input('name_first');
        $user->name_last = $request->input('name_last');
        $user->phone = $request->input('phone');
        $user->email = $request->input('email');
        $user->role_id = $request->input('role_id');
        $user->address = $request->input('address');
        
        if ($user->valid() && $user->save()) {
            return $this->respond('CREATED', $user);
        } else {
            $errors = $user->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error creating user record.',
                    'errors' => $errors
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
        if ($request->has('email')) {
            $user->email = $request->input('email');
        }
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
