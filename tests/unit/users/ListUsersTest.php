<?php

namespace Tests\Unit\Users;

use Mockery;
use App\Http\Services\AuthService;
use \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use WithoutMiddleware;
use Tests\Helpers\AuthMockHelper;

class ListUsersTest extends \TestCase
{

    /**
     * Test empty users paginated list response
     *
     * @return void
     */
    public function testEmptyPaginatedListResponse()
    {
        $service = new AuthService();
        $service->setManagement(AuthMockHelper::managementMock());
        $service->setAuthentication(AuthMockHelper::authenticationMock());
        $response = $service->getAllUsers([]);

        $this->assertEquals(
            $response,
            [
                'page' => 1,
                'per_page' => 10,
                'total' => 0,
                'data' => []
            ]
        );
    }

    /**
     * Test successfull users paginated list response
     *
     * @return void
     */
    public function testSuccessfullPaginatedListResponse()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());
        $user = AuthMockHelper::userResponse();

        $this->json('GET', '/users')
            ->seeJson(
                [
                    "data" => [$user, $user, $user],
                    "meta" => [
                        "pagination" => [
                            "count" => 3,
                            "current_page" => 1,
                            "links" => [
                                "next" => url("/users?page=2")
                            ],
                            "per_page" => 1,
                            "total" => 3,
                            "total_pages" => 3
                        ]
                    ]
                ]
            )
            ->seeStatusCode(200);
    }
}
