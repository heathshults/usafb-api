<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use App\Models\Enums\Role;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Helpers\PaginationHelper;

/**
 * UsersController
 * Manage users information
 *
 * @package    Http
 * @subpackage Controllers
 * @author     Daylen Barban <daylen.barban@bluestarsports.com>
 */
class UsersController extends Controller
{
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
        $user = $request->user();
        $isUSSFStaff = app('Auth')->isSuperUser($user);

        $validationRules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'numeric'
        ];
        $role = "";
        if ($isUSSFStaff) {
            $validationRules['role'] = 'required';
            $role = Role::label($request->input('role'));
        }
        $this->validate(
            $request,
            $validationRules
        );

        $user = [
            'email' => $request->input('email'),
            'connection' => getenv('AUTH_CONNECTION'),
            'password' => substr(hash('sha512', rand()), 0, 8),
            'user_metadata' => [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'city' => $request->input('city'),
                'phone_number' => $request->input('phone_number'),
                'state' => $request->input('state'),
                'postal_code' => $request->input('postal_code'),
                'roles' => [$role],
                'created_by' => $user['id']
            ]
        ];
        return app('Auth')->createUser($user);
    }

    /**
     * Get paginated list of users including sorting and search by criteria
     * Url: GET /users
     *
     * @param Request $request
     *
     * @return json
     */
    public function getAll(Request $request)
    {
        $criteria = $request->query();
        return app('Auth')->getAllUsers($criteria);
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
    public function delete(Request $request, $id)
    {
        return app('Auth')->deleteUser($id);
    }

    /**
     * Get User by id
     * Url: GET /users/{id}
     *
     * @param Request $request
     * @param Request $id
     *
     * @return json
     */
    public function getById(Request $request, $id)
    {
        return app('Auth')->getUserById($id);
    }

    /**
     * Update user
     * Url: PUT /users/{id}
     *
     * @param Request $request
     * @param string $id User id
     *
     * @return json
     */
    public function update(Request $request, $id)
    {
        if (empty($id)) {
            throw new NotFoundHttpException("User id required.");
        }

        $data = $request->all();
        if (empty($data)) {
            throw new BadRequestHttpException("Data required.");
        }

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'numeric',
            'password' => ['required', 'min:8',
                'regex:/^((?=.*\\d)(?=.*[a-zA-Z])(?=.*[@#$%*]))/']
        ];

        $validationsRules = [];
        foreach ($rules as $field => $rule) {
            if (isset($data[$field])) {
                $validationsRules[$field] = $rule;
            }
        }
        $passwordCustomMessage = [
            'password.regex' => 'Password is too weak.',
        ];
        $this->validate(
            $request,
            $validationsRules,
            $passwordCustomMessage
        );
        $user = $request->user();
        $data['modified_by'] = $user['id'];
        return app('Auth')->updateUser($id, $data);
    }

    /**
     * Get logs by user id
     * Url: GET /users/{id}/logs
     *
     * @param Request $request
     * @param string $userId
     *
     * @return json
     */
    public function getLogs(Request $request, $userId)
    {
        $itemsPerPage = $request->input('per_page') !== null ?
            $request->input('per_page') :
            10;

        $userId = rawurldecode($userId);

        $paginatedResult = app('UserLog')->getPaginatedList($userId, $itemsPerPage);

        return PaginationHelper::paginationResponse(
            $paginatedResult,
            $request->input('per_page')
        );
    }
}
