<?php

namespace Tests\Unit\Helpers;

use App\Helpers\PaginationHelper;

class PaginationHelperTest extends \TestCase
{

    /**
     * Test successfull remove page parameter
     * from the query parameters list to be added to pagination links
     *
     * @return void
     */
    public function testAdditionalQueryParams()
    {
        $queryParams = [
            'per_page' => 2,
            'sort' => '+name',
            'page' => 1
        ];
        $additionalParams = [
            'per_page' => 2,
            'sort' => '+name'
        ];
        $this->assertEquals(
            $additionalParams,
            PaginationHelper::additionalQueryParams($queryParams)
        );

    }
}
