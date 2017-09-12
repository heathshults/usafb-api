<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationHelper
{
    /**
     * Pagination response structure
     *
     * @param Illuminate\Pagination\Paginator $paginatedResult
     * @param int $itemsPerPage
     *
     * @return array links
     */
    public static function paginationResponse($paginatedResult, $itemsPerPage = null)
    {
        if ($paginatedResult->count() === 0) {
            return [];
        }

        $response = [
            "meta" => [
                "total_pages" => $paginatedResult->lastPage(),
                "total_items" => $paginatedResult->total()
            ],
            "data" => $paginatedResult->toArray()['data'],
            "links" => PaginationHelper::createLinksPagination(
                $paginatedResult,
                $itemsPerPage
            )
        ];
        return $response;
    }


    /**
     * Links Array of pages in a paginated list resultant
     * from model paginate function
     * self: current page link, first: first page link
     * prev: previous page link, next: next page link,
     * last: last page link
     *
     * @param Illuminate\Pagination\Paginator $paginatedResult
     * @param int $itemsPerPage
     *
     * @return array links
     */
    public static function createLinksPagination($paginatedResult, $itemsPerPage = null)
    {
        // framework paginate function return LengthAwarePaginator object
        if ($paginatedResult instanceof LengthAwarePaginator) {
            // string to concatenate to pages link if itemsPerPage is provided
            $pageSizeParamStr = "&per_page=";
            $pageSizeParam = $itemsPerPage === null ?
                '' :
                $pageSizeParamStr.$itemsPerPage;

            $prev = null;
            $currentPage = $paginatedResult->currentPage();
            if ($currentPage !== 1) {
                $prev = $paginatedResult->url($currentPage - 1).$pageSizeParam;
            }

            $next = null;
            $lastPage = $paginatedResult->lastPage();
            if ($currentPage !== $lastPage) {
                $next = $paginatedResult->url($currentPage + 1).$pageSizeParam;
            }

            return [
                "self" => $paginatedResult->url($currentPage).$pageSizeParam,
                "first" => $paginatedResult->url(1).$pageSizeParam,
                "prev" => $prev,
                "next" =>  $next,
                "last" => $paginatedResult->url($lastPage).$pageSizeParam
            ];
        }
        return [];
    }
}
