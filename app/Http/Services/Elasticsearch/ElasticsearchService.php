<?php

namespace App\Http\Services\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

use Illuminate\Support\Facades\Log;

use App\Transformers\Elasticsearch\ElasticsearchCoachTransformer;
use App\Transformers\Elasticsearch\ElasticsearchPlayerTransformer;

/**
 * Manage Elasticsearch
 *
 * @package    Http
 * @subpackage Services
 */
class ElasticsearchService
{
    const INDEX_COACHES = 'coaches';
    const INDEX_PLAYERS = 'players';
    
    const TYPE_COACHES = 'coaches';
    const TYPE_PLAYERS = 'players';
    
    protected $client;
    protected $query;
    protected $paginationCriteria;
    protected $searchSort;
        
    /**
     * Initialize Elasticsearch service
     *
     * @constructor
     */
    public function __construct()
    {
        $hosts = explode(',', env('ELASTICSEARCH_HOST', 'elasticsearch'));
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts($hosts);
        $this->client = $clientBuilder->build();
    }
    
    public function indexCoach($id, $body = [])
    {
        $this->indexDocument(self::INDEX_COACHES, self::TYPE_COACHES, $id, $body);
        return true;
    }
    
    public function indexPlayer($id, $body = [])
    {
        $this->indexDocument(self::INDEX_PLAYERS, self::TYPE_PLAYERS, $id, $body);
        return true;
    }
    
    public function deleteCoachIndices()
    {
        return $this->deleteIndices(self::INDEX_COACHES);
    }

    public function deletePlayerIndices()
    {
        return $this->deleteIndices(self::INDEX_PLAYERS);
    }
    
    public function deleteIndices($index)
    {
        try {
            $params = [ 'index' => $index ];
            $exists = $this->client->indices()->exists($params);
            if ($exists) {
                $response = $this->client->indices()->delete($params);
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
    
    public function createPlayerIndices()
    {
        return $this->createIndices(self::INDEX_PLAYERS);
    }

    public function createCoachIndices()
    {
        return $this->createIndices(self::INDEX_COACHES);
    }
    
    public function createIndices($index)
    {
        $params = [
            'index' => $index,
            'body' => [
                'mappings' => [
                    $index => [
                        'properties' => [
                            'id_external' => [ 'type' => 'text', 'fielddata' => true ],
                            'id_usafb' => [ 'type' => 'text', 'fielddata' => true ],
                            'name_first' => [ 'type' => 'text', 'fielddata' => true ],
                            'name_last' => [ 'type' => 'text', 'fielddata' => true ],
                            'gender' => [ 'type' => 'text', 'fielddata' => true ],
                            'dob' => [ 'type' => 'text', 'fielddata' => true ],
                            'city' => [ 'type' => 'text', 'fielddata' => true  ],
                            'state' => [ 'type' => 'text', 'fielddata' => true ],
                            'county' => [ 'type' => 'text', 'fielddata' => true ],
                            'postal_code' => [ 'type' => 'text', 'fielddata' => true ]
                        ]
                    ]
                ]
            ]
        ];
        try {
            $response = $this->client->indices()->create($params);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
    
    public function indexDocument($index, $type, $id, $body = [])
    {
        Log::debug('ElasticsearchService > indexDocument('.$id.')');
        try {
            // TODO check connection and reconnect if necessary?
            $response = $this->client->index([
                'index' => $index,
                'type' => $type,
                'id' => $id,
                'body' => $body
            ]);
            $is_success = ( $response != null && $response['result'] == 'created' ? true : false );
            Log::debug('ElasticsearchService > indexDocument('.$id.') response ('.$is_success.')');
            return $is_success;
        } catch (Exception $ex) {
            Log::error($ex);
            throw new ElasticsearchException($ex->getMessage());
        }
    }
    
    public function deleteDocument($index, $type, $id)
    {
        Log::debug('ElasticsearchService > deleteDocument('.$id.')');
        try {
            // TODO check connection and reconnect if necessary?
            $response = $this->client->delete([
                'index' => $index,
                'type' => $type,
                'id' => $id
            ]);
            $is_success = ( $response != null && $response['result'] == 'deleted' ? true : false );
            Log::debug('ElasticsearchService > deleteDocument('.$id.') response ('.$is_success.')');
            return $is_success;
        } catch (Exception $ex) {
            Log::error('Error occurred while removing document ('.$id.') from Elasticsearch.');
            Log::error($ex);
            throw new ElasticsearchException($ex->getMessage());
        }
    }
    
    public function deleteDocuments($index, $type, $ids = [])
    {
        Log::debug('ElasticsearchService > deleteDocuments('.implode(',', $ids).')');
        if ($ids == null || ($ids != null && empty($ids))) {
            return false;
        }
        try {
            // TODO check connection and reconnect if necessary?
            $request = [
                'index' => $index,
                'type' => $type,
                'body' => []
            ];
            // build bulk delete payload for ids[]
            foreach ($ids as $id) {
                $request['body'][] = [
                    'delete' => [
                        '_index' => $index,
                        '_type' => $type,
                        '_id' => $id
                    ]
                ];
            }
            $response = $this->client->bulk($request);
            return true;
        } catch (Exception $ex) {
            Log::error('ElasticsearchService > Error occurred: '.$ex->getMessage());
            Log::error($ex);
            throw new ElasticsearchException($ex->getMessage());
        }
    }
    
    public function deleteAllDocuments($index, $type)
    {
        try {
            $response = $this->client->indices()->deleteMapping([ 'index' => $index, 'type' => $type ]);
            Log::debug('ElasticsearchService > deleteAllDocumnents(..) response: '.var_export($response, true));
            return true;
        } catch (Exception $ex) {
            Log::error('ElasticsearchService > Error occurred: '.$ex->getMessage());
            Log::error($ex);
            throw new ElasticsearchException($ex->getMessage());
        }
    }
    
    public function searchPlayers($options = [])
    {
        Log::debug('ElasticsearchService > searchPlayers( ... )');
        $response = $this->search(self::INDEX_PLAYERS, self::TYPE_PLAYERS, $options);
        $response->setTransformer(new ElasticsearchPlayerTransformer());
        return $response;
    }

    public function searchCoaches($options = [])
    {
        Log::debug('ElasticsearchService > searchCoaches( ... )');
        $response = $this->search(self::INDEX_COACHES, self::TYPE_COACHES, $options);
        $response->setTransformer(new ElasticsearchPlayerTransformer());
        return $response;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getPage()
    {
        return (!is_null($this->paginationCriteria) ? $this->paginationCriteria['page'] : 1);
    }

    public function getPageSize()
    {
        return (!is_null($this->paginationCriteria) ? $this->paginationCriteria['per_page'] : 50);
    }

    public function getPageFrom()
    {
        $pageFrom = (!is_null($this->paginationCriteria) ? $this->paginationCriteria['from'] : 0);
        // Elasticsearch max response size for this index
        if ($pageFrom >= 10000) {
            $pageFrom = (10000 - $this->getPageSize());
        }
        return $pageFrom;
    }

    public function setSearchPageSize($size)
    {
        $this->searchPageSize = $size;
    }

    public function setPaginationCriteria($paginationCriteria)
    {
        $this->paginationCriteria = $paginationCriteria;
    }

    public function setSearchSort($field, $direction)
    {
        $this->searchSort = [ $field => $direction ];
    }

    public function setQuery($query)
    {
        Log::debug('ElasticsearchService > setQuery(.) - Setting query.');
        $this->query = $query;
    }

    protected function search($index, $type, $options = [])
    {
        Log::debug('Query: '.$this->getQuery());

        $response = new ElasticsearchResponse();

        // TODO check connection and reconnect if necessary?
        $searchParameters = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'size' => $this->getPageSize(),
                'from' => $this->getPageFrom(),
                'query' => $this->getQuery()->toArray()
            ]
        ];
        
        if (!is_null($this->searchSort)) {
            $searchParameters['body']['sort'] = $this->searchSort;
        }
        
        if ($options != null) {
            if (array_key_exists('from', $options)) {
                $searchParameters['body']['from'] = $options['from'];
            }
        }

        $response->setLimit($searchParameters['body']['size']);
        $response->setStart($searchParameters['body']['from']);

        try {
            $searchResponse = $this->client->search($searchParameters);
            if (!is_null($searchResponse['timed_out']) && !$searchResponse['timed_out']) {
                $response->setSuccessful();
                $response->setSearchResponse($searchResponse);
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
        
        return $response;
    }
}
