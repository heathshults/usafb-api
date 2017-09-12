<?php

namespace Tests\Unit;

use Mockery;
use App\Http\Services\AuthService;
use \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use WithoutMiddleware;
use Tests\Helpers\MockHelper;

class ListUsersTest extends \TestCase
{

    /**
     * Test successfull extraction of sort field from expression
     * in ascending order
     *
     * @return void
     */
    public function testExtractSortFieldAsc()
    {
        $service = new AuthService();

        $sortExpression = "name";
        $response = $service->extractSortField($sortExpression);
        $this->assertEquals($sortExpression.":1", $response);
    }

    /**
     * Test successfull extraction of sort field from expression
     * in descending order
     *
     * @return void
     */
    public function testExtractSortFieldDesc()
    {
        $service = new AuthService();

        $sortExpression = "-name";
        $response = $service->extractSortField($sortExpression);
        $this->assertEquals("name:-1", $response);
    }

    /**
     * Test successfull extraction of sort field from expression
     * in ascending order
     *
     * @return void
     */
    public function testExtractSortFieldAscSpecified()
    {
        $service = new AuthService();

        $sortExpression = "+name";
        $response = $service->extractSortField($sortExpression);
        $this->assertEquals("name:1", $response);
    }

    /**
     * Test pagination links self and first are the same for the first page
     *
     * @return void
     */
    public function testCreateLinksPaginationSelfEqualFirst()
    {
        $service = new AuthService();

        $response = $service->createLinksPagination(0, 10, 1);

        $this->assertEquals(
            $response['self'],
            $response['first']
        );
    }

    /**
     * Test pagination link previous is null for the first page
     *
     * @return void
     */
    public function testCreateLinksPaginationNullPrev()
    {
        $service = new AuthService();

        $response = $service->createLinksPagination(0, 10, 1);

        $this->assertEquals($response["prev"], null);
    }

    /**
     * Test pagination link next is null for the last page
     *
     * @return void
     */
    public function testCreateLinksPaginationNullNext()
    {
        $service = new AuthService();

        $response = $service->createLinksPagination(0, 10, 1);

        $this->assertEquals($response['next'], null);
    }

    /**
     * Test pagination link last is equals self link when only one page exists
     *
     * @return void
     */
    public function testCreateLinksPaginationLastEqualsSelf()
    {
        $service = new AuthService();

        $response = $service->createLinksPagination(0, 10, 1);

        $this->assertEquals($response['last'], $response['self']);
    }

    /**
     * Test pagination link last is equals self link when only one page exists
     *
     * @return void
     */
    public function testCreateLinksPaginationResponse()
    {
        $service = new AuthService();

        $response = $service->createLinksPagination(0, 10, 1);
        $link = getenv('HOSTNAME').'/users?page[number]=0&page[size]=10';
        $this->assertEquals(
            [
                "self" => $link,
                "first" => $link,
                "prev" => null,
                "next"=> null,
                "last"=> $link
            ],
            $response
        );
    }

    /**
     * Test successfull users paginated list response
     *
     * @return void
     */
    public function testSuccessfullPaginatedListResponse()
    {
        $service = new AuthService();
        $userList = [
            'users' => [MockHelper::userResponse()],
            'total' => 1
        ];
        $service->setManagement(MockHelper::managementMock([], $userList));
        $service->setAuthentication(MockHelper::authenticationMock());
        $response = $service->getAllUsers([]);

        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('total_pages', $response['meta']);
        $this->assertArrayHasKey('total_users', $response['meta']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('links', $response);
    }

    /**
     * Test empty users paginated list response
     *
     * @return void
     */
    public function testEmptyPaginatedListResponse()
    {
        $service = new AuthService();
        $service->setManagement(MockHelper::managementMock());
        $service->setAuthentication(MockHelper::authenticationMock());
        $response = $service->getAllUsers([]);

        $this->assertEquals($response, []);
    }
}
