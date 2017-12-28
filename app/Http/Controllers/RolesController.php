<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Transformers\RolePermissionTransformer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

/**
 * RolesController
 * Manage roles
 *
 * @package    Http
 * @subpackage Controllers
 */
class RolesController extends Controller
{

    /**
     * Get roles
     * Url: GET /roles
     *
     * @param Request $request
     *
     * @return json
     */
    public function index(Request $request)
    {
        $sort = $this->buildSortCriteria($request->query(), [ 'column' => 'name', 'order' => 'asc' ]);
        $paginationCriteria = $this->buildPaginationCriteria($request->query());
        $roles = Role::orderBy($sort['column'], $sort['order'])->paginate($paginationCriteria['per_page']);
        return $this->respond('OK', $roles);
    }

    /**
     * Get role
     * Url: GET /roles/:id
     *
     * @param Request $request
     * @param string $id
     *
     * @return json
     */
    public function show(Request $request, $id)
    {
        $role = Role::find($id);
        if (is_null($role)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Role ('.$id.') not found.']]);
        }
        return $this->respond('OK', $role);
    }

    /**
     * Update role
     * Url: PUT /roles/{id}
     *
     * @param Request $request
     * @param string $roleId Role ID
     *
     * @return json
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if (is_null($role)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Role ('.$id.') not found.']]);
        }
        if ($request->has('name')) {
            $role->name = $request->input('name');
        }
        if ($request->has('permissions')) {
            $role->permissions = $request->input('permissions');
        }
        if ($role->valid() && $role->save()) {
            return $this->respond('ACCEPTED', $role);
        } else {
            $errors = $role->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error updating role record.',
                    'errors' => $role->errors()
                ]
            ]);
        }
    }
    
    /**
     * Create role
     * Url: POST /roles
     *
     * @param Request $request
     *
     * @return json
     */
    public function create(Request $request)
    {
        $role = new Role();
        $role->name = $request->input('name');
        $role->permissions = $request->input('permissions');
        if ($role->valid() && $role->save()) {
            return $this->respond('CREATED', $role);
        } else {
            $errors = $role->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error creating new record.',
                    'errors' => $errors
                ]
            ]);
        }
    }

    /**
     * Get permissions
     * Url: GET /roles/permissions
     *
     * @param Request $request
     *
     * @return json
     */
    public function permissions(Request $request)
    {
        $permissions = Role::PERMISSIONS;
        $transformer = new RolePermissionTransformer();
        $collection = new Collection($permissions, $transformer);
        $results = (new Manager())->createData($collection)->toArray();
        return $this->respond('OK', $results);
    }
}
