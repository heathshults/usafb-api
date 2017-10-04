<?php

namespace App\Http\Services;

use League\Fractal\Pagination\IlluminatePaginatorAdapter;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

/**
 * FractalService
 *
 * @package    Http
 * @subpackage Services
 * @author     Daylen Barban <daylen.barban@bluestarsports.com>
 */
class FractalService
{
    protected $fractal;

    /**
     * Initialize auth service
     *
     * @constructor
     */
    public function __construct(Manager $fractal)
    {
        $this->fractal = $fractal;
    }

    /**
     * Response of paginated collection
     *
     * @param array $list
     * @param object $transformer Fractal Transformer
     * @param Illuminate\Pagination\LengthAwarePaginator $paginator
     *
     * @return array
     */
    public function paginatedCollection($list, $transformer, $paginator = null, $queryParams = [])
    {
        $collection = new Collection($list, $transformer);

        $paginator = is_null($paginator) ? $list : $paginator;
        if (!empty($queryParams)) {
            $paginator->appends($queryParams);
        }
        $collection->setPaginator(new IlluminatePaginatorAdapter($paginator));
        return $this->fractal->createData($collection)->toArray();
    }

    /**
     * Response for a simple model
     *
     * @param object $model
     * @param object $transformer Fractal Transformer
     *
     * @return array
     */
    public function item($model, $transformer)
    {
        $item = new Item($model, $transformer);
        return $this->fractal->createData($item)->toArray();
    }
}
