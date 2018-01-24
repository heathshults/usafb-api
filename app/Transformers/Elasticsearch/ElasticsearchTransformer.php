<?php

namespace App\Transformers\Elasticsearch;

use App\Models\Player;
use App\Models\PlayerRegistration;

use Illuminate\Support\Facades\Log;

class ElasticsearchTransformer
{

    /**
     * Returns theu7  player response based on an elasticsearch response
     *
     * @param array $results from Elasticsearch client
     *
     * @return array
     */
    /**
     * Returns a transformed player response based on an elasticsearch response
     *
     * @param array $results from Elasticsearch client transformed
     *
     * @return array
     */
    public function transform($results)
    {
        $response = [];
        foreach ($results['hits']['hits'] as $result) {
            $id = $result['_id'];
            $source = $result['_source'];
            $record = [
                'id' => $id,
                'id_usafb' => $source['id_usafb'],
                'name_first' => $source['name_first'],
                'name_middle' => $source['name_middle'],
                'name_last' => $source['name_last'],
                'dob' => $source['dob'],
                'gender' => $source['gender'],
                'city' => $source['city'],
                'state' => $source['state'],
                'county' => $source['county'],
                'postal_code' => $source['postal_code'],
                'level_type' => $source['level_type'],
                'level' => $source['level'],
                'position' => $source['position'],
            ];
            $response[] = $record;
        }
        return $response;
    }
}
