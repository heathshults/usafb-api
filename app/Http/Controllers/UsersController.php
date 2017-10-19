<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Enums\Role;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Helpers\PaginationHelper;
use App\Helpers\AuthHelper;
use App\Transformers\UserTransformer;
use App\Transformers\UserLogTransformer;

use League\fractalService\Pagination\IlluminatePaginatorAdapter;
use League\fractalService\Manager;
use League\fractalService\Resource\Collection;
use League\fractalService\Resource\Item;
use App\Models\Enums\LogEvent;

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

    protected $authService;
    protected $fractalService;
    protected $userTransformer;
    protected $logsService;
    protected $userLogTransformer;

    /**
     * Initialize auth service
     *
     * @constructor
     */
    public function __construct()
    {
        $this->authService = app('Auth');
        $this->fractalService = app('Fractal');
        $this->logsService = app('UserLog');

        $this->userTransformer = new UserTransformer();
        $this->userLogTransformer = new UserLogTransformer();
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
        $user = $request->user();
        $roles = implode(",", Role::allRoles());

        $validationRules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'numeric',
            'role' => 'required|in:'.$roles
        ];

        $this->validate(
            $request,
            $validationRules,
            [
                "role.in" => "The role should be one the defined types (".$roles.")"
            ]
        );
        $newUser = [
            'email' => $request->input('email'),
            'connection' => getenv('AUTH_CONNECTION'),
            'password' => substr(hash('sha512', rand()), 0, 8),
            'user_metadata' => [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'country' => $request->input('country'),
                'city' => $request->input('city'),
                'phone_number' => $request->input('phone_number'),
                'state' => $request->input('state'),
                'postal_code' => $request->input('postal_code'),
                'organization' => $request->input('organization'),
                'address' => $request->input('address'),
                'address2' => $request->input('address2'),
                'roles' => [$request->input('role')],
                'created_by' => $user->getId()
            ]
        ];

        $userResponse = $this->authService->createUser($newUser);
        $this->logsService->create(LogEvent::CREATE, $user, $userResponse);

        return $this->fractalService->item($userResponse, $this->userTransformer);
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
        $listResponse = $this->authService->getAllUsers($criteria);
        $list = $listResponse['data'];
        $usersPaginator = new LengthAwarePaginator(
            $list,
            $listResponse['total'],
            $listResponse['per_page'],
            $listResponse['page'],
            [
                'path' => $request->url()
            ]
        );

        return $this->fractalService->paginatedCollection(
            $list,
            $this->userTransformer,
            $usersPaginator,
            PaginationHelper::additionalQueryParams($request->all())
        );
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
        return $this->authService->deleteUser($id);
    }

    /**
     * Get User by id
     * Url: GET /users/{id}
     *
     * @param Request $request
     * @param Request $userId
     *
     * @return json
     */
    public function getById(Request $request, $userId)
    {
        $user = $this->authService->getUserById($userId);
        return $this->fractalService->item($user, $this->userTransformer);
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
    public function update(Request $request, $userId)
    {
        if (empty($userId)) {
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
        $loggedUser = $request->user();
        $data['updated_by'] = $loggedUser->getId();

        $previousUser = $this->authService->getUserById($userId);
        $updatedUser = $this->authService->updateUser($userId, $data);

        $this->logsService->create(LogEvent::UPDATE, $loggedUser, $updatedUser, $previousUser);

        return $this->fractalService->item($updatedUser, $this->userTransformer);
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

        $list = $this->logsService->getPaginatedList($userId, $itemsPerPage);
        return $this->fractalService->paginatedCollection(
            $list,
            $this->userLogTransformer,
            null,
            PaginationHelper::additionalQueryParams($request->all())
        );
    }
}
