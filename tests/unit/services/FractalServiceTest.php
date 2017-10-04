<?php

namespace Tests\Unit\Services;

use Tests\Helpers\UserLogMockHelper;
use App\Transformers\UserLogTransformer;
use App\Http\Services\FractalService;
use League\Fractal\Manager;
use Illuminate\Pagination\LengthAwarePaginator;

class FractalServiceTest extends \TestCase
{
    protected static $service;

    public static function setUpBeforeClass()
    {
        $manager = new Manager();
        self::$service = new FractalService($manager);
    }

    /**
     * Test valid structure of a paginated list response
     *
     * @return void
     */
    public function testPaginatedCollectionStructure()
    {
        $log = UserLogMockHelper::userLogData();
        $list = [$log, $log, $log];
        $transformer = new UserLogTransformer();

        $paginator = new LengthAwarePaginator(
            $list, count($list), 1, 1, [
                'path' => 'http://example.com/logs'
            ]
        );

        $response = self::$service->paginatedCollection($list, $transformer, $paginator);

        $logResponse = UserLogMockHelper::userLogResponse();
        $listResponse = [$logResponse, $logResponse, $logResponse];

        $paginationMeta = $response['meta']['pagination'];
        $this->assertEquals($response['data'], $listResponse);
        $this->assertEquals($paginationMeta['total'], 3);
        $this->assertEquals($paginationMeta['count'], 3);
        $this->assertEquals($paginationMeta['per_page'], 1);
        $this->assertEquals($paginationMeta['current_page'], 1);
        $this->assertEquals($paginationMeta['total_pages'], 3);
        $this->assertEquals($paginationMeta['links']['next'], "http://example.com/logs?page=2");
    }

    /**
     * Test query parameters added to pagination links
     *
     * @return void
     */
    public function testPaginatedCollectionQueryParamsAdded()
    {
        $log = UserLogMockHelper::userLogData();
        $list = [$log, $log, $log];
        $transformer = new UserLogTransformer();

        $paginator = new LengthAwarePaginator(
            $list, count($list), 1, 1, [
                'path' => 'http://example.com/logs'
            ]
        );
        $queryParams = [
            'per_page' => 2
        ];

        $response = self::$service->paginatedCollection($list, $transformer, $paginator, $queryParams);

        $this->assertEquals(
            $response['meta']['pagination']['links']['next'],
            "http://example.com/logs?per_page=2&page=2"
        );
    }

    /**
     * Test valid structure when list of results is empty
     *
     * @return void
     */
    public function testPaginatedCollectionStructureEmptyList()
    {
        $transformer = new UserLogTransformer();

        $paginator = new LengthAwarePaginator(
            [], 0, 1, 1, [
                'path' => 'http://example.com/logs'
            ]
        );

        $response = self::$service->paginatedCollection([], $transformer, $paginator);

        $paginationMeta = $response['meta']['pagination'];
        $this->assertEquals($response['data'], []);
        $this->assertEquals($paginationMeta['total'], 0);
        $this->assertEquals($paginationMeta['count'], 0);
        $this->assertEquals($paginationMeta['per_page'], 1);
        $this->assertEquals($paginationMeta['current_page'], 1);
        $this->assertEquals($paginationMeta['total_pages'], 0);
        $this->assertEquals($paginationMeta['links'], []);
    }
}
