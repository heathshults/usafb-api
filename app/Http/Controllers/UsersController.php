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
        $user->id_external = $request->input('id_external');
        $user->name_first = $request->input('name_first');
        $user->name_last = $request->input('name_last');
        $user->phone = $request->input('phone');
        $user->email = $request->input('email');
        $user->role_id = $request->input('role_id');
                
        // TODO validate user
        if ($user->save()) {
            return $this->respond('CREATED', $user);
        }
        
        return $this->respond('INVALID', ['error' => ['message' => 'Error creating new user record.']]);
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
        $data = $request->all();
        if (isset($data) && sizeof($data) > 0) {
            // TODO validate
            if ($user->update($data)) {
                return $this->respond('ACCEPTED', $user);
            }
        }
        return $this->respond('NOT_MODIFIED', $user);
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
        if ($user->activate()) {
            return $this->respond('ACCEPTED', $user);
        } else {
            return $this->respond('NOT_MODIFIED', $user);
        }
    }
}
