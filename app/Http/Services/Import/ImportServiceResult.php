<?php

namespace App\Http\Services\Import;

/**
 * ImportServiceResult
 * Import player result container
 *
 * @package    Http
 * @subpackage Services
 */

class ImportServiceResult
{
    public $numRecords = 0;
    
    protected $errors = [];
    protected $results = [];

    public function addResult($rowNum, $recordId, $externalId, $usafbId, $nameFirst, $nameMiddle, $nameLast)
    {
        $this->results[] = [
            'row' => $rowNum,
            'id' => $recordId,
            'id_external' => $externalId,
            'id_usafb' => $usafbId,
            'name_first' => $nameFirst,
            'name_middle' => $nameMiddle,
            'name_last' => $nameLast
        ];
        return;
    }
    
    public function addErrors($rowNum, $errors)
    {
        $this->errors[] = [
            'row' => $rowNum,
            'errors' => $errors
        ];
        return;
    }
    
    public function results()
    {
        return $this->results;
    }
    
    public function numImported()
    {
        return count($this->results);
    }
    
    public function errors()
    {
        return $this->errors;
    }
        
    public function numErrors()
    {
        return count($this->errors);
    }
}
