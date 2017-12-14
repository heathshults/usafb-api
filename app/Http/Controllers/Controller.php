<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Helpers\HttpHelper;

class Controller extends BaseController
{
    // Status codes & corresponding strings
    protected $statusCodes = [
        'OK' => 200,
        'CREATED' => 201,
        'ACCEPTED' => 202,
        'DELETED' => 204,
        'NOT_MODIFIED' => 304,
        'INVALID' => 400,
        'NOT_FOUND' => 404,
        'NOT_ALLOWED' => 401
    ];

    protected function buildSearchCriteria(array $criteria)
    {
        return $criteria['q'] ?? null;
    }

    protected function buildPaginationCriteria(array $criteria)
    {
        $perPage = $criteria['per_page'] ?? 10;
        return intval($perPage);
    }

    protected function buildSortCriteria(array $criteria)
    {
        $sort = $criteria['sort'] ?? null;
        if ($sort) {
            $sort = HttpHelper::extractSortParams($sort);
        }

        return $sort;
    }

    protected function paginateCollection(Collection $collection, $perPage = 10, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        return new LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $collection->count(),
            $perPage,
            $page,
            $options
        );
    }

    /**
     * Helper that provides response as formatted JSON
     * @param string $status
     * @param array $data
     * @return mixed
     */
    protected function respond($status, $data = [])
    {
        return response()->json($data, $this->statusCodes[$status]);
    }
}