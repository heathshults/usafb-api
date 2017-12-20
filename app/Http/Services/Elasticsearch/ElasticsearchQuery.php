<?php

namespace App\Http\Services\Elasticsearch;

use Illuminate\Support\Facades\Log;
use App\Helpers\HttpHelper;

abstract class ElasticsearchQuery
{
    protected $fields;
    protected $ranges;
    
    /**
     * Constructor
     *
     * @param array $$criteria containing HTTP parameters from Controller
     *
     * @return instance of ElasticsearchQuery
     */
    public function __construct(array $criteria)
    {
        $this->fields = [];
        $this->ranges = [];
                
        // verify that fields are supported, separete must-match vs range fields
        foreach ($criteria as $name => $value) {
            if (in_array($name, $this->supportedFields) && $value != '') {
                $this->fields[$name] = $value;
            } elseif (in_array($name, $this->rangeFields)) {
                $this->ranges[$name] = $value;
            }
        }
    }
        
    /**
     * Returns true/false if there are any range fields defined
     *
     * @return boolean
     */
    public function hasRanges()
    {
        return (sizeof($this->ranges) > 0);
    }

    /**
     * Returns true/false if there is 1 or more fields
     *
     * @return boolean
     */
    public function hasFields()
    {
        return (sizeof($this->fields) > 0);
    }

    /**
     * Returns true/false if there is enough data for query
     *
     * @return boolean
     */
    public function isValid()
    {
        if (sizeof($this->fields) <= 0 && sizeof($this->ranges) <= 0) {
            return false;
        }
        return true;
    }
    
    public function toArray()
    {
        if ($this->isValid()) {
            $query = [];
            if ($this->hasFields()) {
                Log::debug('Compiling boolean matches.');
                $query['bool'] = [];
                $query['bool']['must'] = [];
                foreach ($this->fields as $name => $value) {
                    $query[ "bool" ]['must'][] = [ 'match' => [ $name => $value ] ];
                }
            }
            if ($this->hasRanges()) {
                // TODO
            }
            return $query;
        } else {
            return null;
        }
    }
    
    public function __toString()
    {
        return 'ElasticsearchQuery > '.var_export($this->fields, true);
    }
}
