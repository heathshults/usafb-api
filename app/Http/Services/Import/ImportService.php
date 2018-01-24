<?php

namespace App\Http\Services\Import;

use App\Exceptions\ImportServiceException;
use App\Models\Player;
use App\Models\Coach;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;
use Pitpit\Component\Diff\DiffEngine;

/**
 * ImportService
 * Import players and coaches
 *
 * @package    Http
 * @subpackage Services
 */

abstract class ImportService
{
    // set consts in child class
    const SOURCE_NUM_MAX_RECORDS = 0;
    const SOURCE_NUM_COLUMNS = 0;
    const COLUMNS = [];
    
    protected $skipSaving = false;
    
    // return new record/object (what we're importing)
    abstract public function newRecord();

    // create new record (Player or Coach) from source data using column mappings
    public function buildRecord($csv)
    {
        $record = $this->newRecord();
        foreach ($this::COLUMNS as $csvColumn => $modelField) {
            $field = $modelField['field'];
            // set column value to null if column does not exist
            $value = ($csv[$csvColumn] ?? null);
            if (!empty($value)) {
                // trim all values
                $value = trim($value);
                // if type specified, format value(s) accordingly
                if (array_key_exists('type', $modelField)) {
                    if ($modelField['type'] == 'number') {
                        $record[$field] = (int)$value;
                    } elseif ($modelField['type'] == 'array') {
                        $record[$field] = explode(',', $value);
                    }
                } else {
                    $record[$field] = $value;
                }
            }
        }
        return $record;
    }
    
    // import and process all files within array
    public function importFiles($files) : array
    {
        $results = [];
        foreach ($files as $file) {
            try {
                $result = $this->importFile($file);
                $results[] = $result;
            } catch (Exception $ex) {
                Log::error('Exception occurred importing file ('.$file.')');
            }
        }
        return $results;
    }
         
    public function importFromReader($csvReader) : ImportServiceResult
    {
        $result = new ImportServiceResult();
        $csvReader->setHeaderOffset(0);
        $csvHeader = $csvReader->getHeader();
        // header validation, throws ImportServiceException with details of failure
        $this->validateHeader($csvHeader);
        $row = 0;
        foreach ($csvReader as $csv) {
            // fail-out file if # of parsed columns < $this->numColumns()
            if (count($csv) < $this::SOURCE_NUM_COLUMNS) {
                throw new ImportServiceException('CSV contains < than ('.$this::SOURCE_NUM_COLUMNS.') fields.');
            }
            try {
                $row++;
                $record = $this->buildRecord($csv);
                $result->numRecords++;
                //Log::debug('Record: '.var_export($record,true));
                if ($this->validateRecord($record)) {
                    // Check for duplicates
                    // if skip, skip saving record
                    if (!$this->skipSaving) {
                        if ($record->save()) {
                            $result->addResult(
                                $row,
                                $record->id,
                                $record->id_external,
                                $record->id_usafb,
                                $record->name_first,
                                $record->name_middle,
                                $record->name_last
                            );
                            $result->numImported++;
                        }
                    } else {
                        $result->addResult($row, $record->id, $record->id_usafb);
                    }
                } else {
                    $errors = $record->errors()->toArray();
                    $compiledErrors = $this->compileErrors($errors);
                    if (!is_null($compiledErrors) && count($compiledErrors) > 0) {
                        $result->addErrors($row, $compiledErrors);
                    } else {
                        $result->addErrors($row, 'Unknown error');
                    }
                }
            } catch (Exception $ex) {
                Log::error('Exception occurred: '.$ex->getMessage());
                $result->addErrors($row, [ $ex->getMessage() ]);
            }
        }
        return $result;
    }
     
    // import records from content/string
    public function importContent($content) : ImportServiceResult
    {
        if (is_null($content)) {
            throw new ImportServiceException('Error occurred during import. Invalid content specifeid.');
        }
        try {
            $csvReader = Reader::createFromString($content);
            $result = $this->importFromReader($csvReader);
            return $result;
        } catch (ImportServiceException $importServiceException) {
            throw $importServiceException;
        } catch (Exception $exception) {
            throw new ImportServiceException('Error occurred during import.');
        }
    }
     
    // import records from file
    public function importFile($file) : ImportServiceResult
    {
        if (!file_exists($file)) {
            throw new ImportServiceException('Error occurred during import. File ('.$file.') not found.');
        }
        // file validation (high level), throws ImportServiceException with details of failure
        $this->validateFile($file);
        // process/read csv file
        $csvReader = Reader::createFromPath($file, 'r');
        $result = $this->importFromReader($csvReader);
        return $result;
    }
        
    // compile & cleanup model (laravel validation) error messages
    protected function compileErrors($errors)
    {
        $compiledErrors = [];
        if (!is_null($errors) && count($errors) > 0) {
            foreach (array_keys($errors) as $field) {
                $fieldClean = preg_replace('/^(.+)\.\d+$/', '${1}', $field);
                $fieldErrors = $errors[$field];
                if (count($fieldErrors) > 0) {
                    $fieldErrors = array_map(function ($value) {
                        $value = preg_replace('/(\.\d+)+/', '', $value);
                        return $value;
                    }, $fieldErrors);
                    $compiledErrors[] = implode(', ', $fieldErrors);
                }
            }
        }
        return $compiledErrors;
    }
    
    public function skipSaving($skipSaving)
    {
        $this->skipSaving = $skipSaving;
    }
        
    // supported file extensions .txt or .csv
    public function fileExtensionSupported($fileName)
    {
        $pathInfo = pathinfo($fileName);
        $extension = $pathInfo['extension'];
        return in_array($extension, [ 'txt', 'csv' ]);
    }
    
    // high-level file validation - throws ImportServiceException
    public function validateFile($file)
    {
        $fp = file($file);
        $numRecords = count($fp);
        if ($numRecords > $this::SOURCE_NUM_MAX_RECORDS) {
            throw new ImportServiceException('File contains ('.$numRecords.'). Max is ('.SOURCE_NUM_MAX_RECORDS.')');
        }
    }
    
    // validate header - throws ImportServiceException with details
    public function validateHeader($headerColumns)
    {
        $allowedColumns = array_keys($this::COLUMNS);
        $invalidColumns = array_diff($headerColumns, $allowedColumns);
        if (count($invalidColumns) > 0) {
            throw new ImportServiceException('Invalid columns provided ('.implode(', ', $invalidColumns).')');
        }
    }
    
    // validate record (from reference aka call model validations)
    public function validateRecord(&$record)
    {
        return $record->valid();
    }
}
