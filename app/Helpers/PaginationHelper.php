<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationHelper
{
    /**
     * Returns array of query parameters except page,
     * since page parameters is added by default in paginator
     * This parameters will be added to query parameters in pagination links
     *
     * @param array $queryParams Request query parameters
     *
     * @return array links
     */
    public static function additionalQueryParams($queryParams)
    {
        return array_diff_key($queryParams, array_flip(['page']));
    }
}
