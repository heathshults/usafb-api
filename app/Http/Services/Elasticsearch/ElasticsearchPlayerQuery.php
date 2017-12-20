<?php

namespace App\Http\Services\Elasticsearch;

use Illuminate\Support\Facades\Log;

class ElasticsearchPlayerQuery extends ElasticsearchQuery
{
    protected $sortFields = [
        'id_usafb',
        'name_first',
        'name_last',
        'city',
        'state',
        'dob'
    ];
        
    protected $supportedFields = [
        'id_usafb',
        'name_first',
        'name_last',
        'city',
        'state'
    ];
    
    protected $rangeFields = [
        'dob'
    ];

    public function __toString()
    {
        return var_export($this->fields, true) . ' - '.var_export($this->ranges, true);
    }
}