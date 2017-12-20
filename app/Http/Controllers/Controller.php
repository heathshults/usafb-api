<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Helpers\HttpHelper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
    
    protected function buildPaginationCriteria(array $criteria)
    {
        $perPage = ( $criteria['per_page'] ?? 50 );
        $page = ( $criteria['page'] ?? 1 );
        $from = ( $page == 1 ? 0 : ( $page * $perPage ) );
        
        $pagination = [
            'per_page' => intval($perPage),
            'page' => $page,
            'from' => $from
        ];
        
        return $pagination;
    }

    protected function buildSortCriteria(array $criteria, array $default = [])
    {
        $sort = $criteria['sort'] ?? null;
        if ($sort) {
            $sort = HttpHelper::extractSortParams($sort);
        } elseif (!empty($default)) {
            $sort = $default;
        }
        return $sort;
    }

    protected function buildResponseMeta($total, $perPage, $page)
    {
        $meta = [
            "pagination" => [
                "total" => $total,
                "per_page" => $perPage,
                "current_page" => $page
            ]
        ];
        return $meta;
    }
    
    /**
     * Helper that provides response as formatted JSON
     * @param string $status
     * @param array $data
     * @return mixed
     */
    protected function respond($status, $data)
    {
        $response = [];
        if (array_key_exists('meta', $data) && array_key_exists('data', $data)) {
            $response['meta'] = $data['meta'];
            $response['data'] = $data['data'];
        } else {
            if (array_key_exists('total', $data) && array_key_exists('current_page', $data)) {
                $response['meta'] = $this->buildResponseMeta($data['total'], 0, $data['current_page']);
            }
            if (array_key_exists('data', $data)) {
                $response['data'] = $data['data'];
            } else {
                if (is_a($data, 'Illuminate\Pagination\LengthAwarePaginator')) {
                    $response['meta'] = $this->buildResponseMeta(
                        $data->total(),
                        $data->perPage(),
                        $data->currentPage()
                    );
                    $response['data'] = $data->items();
                } else {
                    $response['data'] = $data;
                }
            }
        }
        return response()->json($response, $this->statusCodes[$status]);
    }
}
