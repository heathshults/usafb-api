<?php

namespace Tests\Unit;

use App\Helpers\PaginationHelper;
use \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Models\Enums\Role;
use WithoutMiddleware;
use Tests\Helpers\MockHelper;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginationHelperTest extends \TestCase
{

    /**
     * Test pagination links self and first are the same for the first page
     *
     * @return void
     */
    public function testCreateLinksPaginationSelfEqualFirst()
    {
        $paginatedResult = new LengthAwarePaginator(
            ['item'], 3, 5, 1
        );
        $response = PaginationHelper::createLinksPagination($paginatedResult, 1);

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
        $paginatedResult = new LengthAwarePaginator(
            ['item'], 3, 5, 1
        );
        $response = PaginationHelper::createLinksPagination($paginatedResult, 1);

        $this->assertEquals($response["prev"], null);
    }

    /**
     * Test pagination link next is null for the last page
     *
     * @return void
     */
    public function testCreateLinksPaginationNullNext()
    {
        $paginatedResult = new LengthAwarePaginator(
            ['item'], 3, 5, 1
        );
        $response = PaginationHelper::createLinksPagination($paginatedResult, 1);

        $this->assertEquals($response['next'], null);
    }

    /**
     * Test pagination link last is equals self link when only one page exists
     *
     * @return void
     */
    public function testCreateLinksPaginationLastEqualsSelf()
    {
        $paginatedResult = new LengthAwarePaginator(
            ['item'], 3, 5, 1
        );
        $response = PaginationHelper::createLinksPagination($paginatedResult, 1);

        $this->assertEquals($response['last'], $response['self']);
    }

    /**
     * Test pagination link last is equals self link when only one page exists
     *
     * @return void
     */
    public function testCreateLinksPaginationResponse()
    {
        $paginatedResult = new LengthAwarePaginator(
            ['item'], 3, 5, 1
        );
        $paginatedResult->setPath(getenv('HOSTNAME').'/users/123/logs');
        $response = PaginationHelper::createLinksPagination($paginatedResult, 1);
        $link = getenv('HOSTNAME').'/users/123/logs?page=1&per_page=1';
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
}
