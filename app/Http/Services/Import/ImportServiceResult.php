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
    public $numImported = 0;
    public $numErrors = 0;
    
    protected $errors = [];
    protected $results = [];

    public function addResult($rowNum, $recordId, $usafbId) {
        $this->results[] = [ 'row' => $rowNum, 'id' => $recordId, 'usafb_id' => $usafbId ];
        return;
    }
    
    public function addErrors($rowNum, $errors) {
        $this->errors[$rowNum] = $errors;
        return;        
    }
    
    public function errors() {
        return $this->errors;
    }
    
    public function errorsForRow($rowNum) {
        if (array_key_exists($rowNum, $this->errors)) {
            return $this->errors[$rowNum];
        }
        return [];
    }

}