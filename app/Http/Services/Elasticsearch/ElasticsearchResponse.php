<?php

namespace App\Http\Services\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Log;

/**
 * Elasticsearch Respose Object
 *
 * @package    Http
 * @subpackage Services
 */
class ElasticsearchResponse
{
    protected $success;
    protected $total;
    protected $start;
    protected $limit;
    protected $response;
    protected $transformer;
    
    public function __construct($transformer = null)
    {
        $this->transformer = $transformer;
        $this->success = false;
        $this->total = 0;
        $this->limit = 0;
        $this->start = 0;
        $this->data = [];
    }
    
    public function setSuccess()
    {
        $this->setSuccessful();
    }
    
    public function setSuccessful()
    {
        $this->success = true;
    }
    
    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
    
    public function setStart($start)
    {
        $this->start = $start;
    }
    
    public function setSearchResponse($response)
    {
        if (!is_null($response)) {
            if (!is_null($response['hits']) && !is_null($response['hits']['total'])) {
                $this->total = $response['hits']['total'];
            }
            $this->response = $response;
        }
    }
        
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
    }
    
    public function toArray()
    {
        $data = (!is_null($this->transformer) ? $this->transformer->transform($this->response) : $this->response);
        $current_page = 1;
        if ($this->start == 0) {
            $current_page = 1;
        } else {
            $current_page = ($this->start / $this->limit);
        }
        $result = [
            "meta" => [
                "pagination" => [
                    "total" => $this->total,
                    "per_page" => $this->limit,
                    "current_page" => $current_page,
                    "start" => $this->start
                ]
            ],
            "data" => $data
        ];
        return $result;
    }
}
