<?php
namespace App\Http\Services\ImportCsv;

/*
 Builder class to contruct a model
*/
class InserterBuilder
{
    private $clazzName;
    private $hashTable = array();
    private $hookFunction;

    private function __construct($clazz)
    {
        $this->clazzName = $clazz;
    }
    /*
        Creates a builder instance for the model passed as param
    */
    public static function createInserterForClass($className)
    {
        return new self($className);
    }
    /*
     Will return a new modal instance
     parsing values according to rules array, 
     Casting and Required are done here
    */
    private function insertValuesForFields()
    {
        $classToCreate = $this->clazzName;
        $modelInstance = new $classToCreate;
        $fields = $this->extractFields();
        $fieldKeys = array_keys($fields);

        $modelWithAttributes = array_reduce($fieldKeys, function ($model, $item) use ($fields) {
            $hashTable = $fields[$item];
            $itemType = $hashTable['type'];
            $value = $hashTable['value'];
            
            if (strpos($itemType, '?') == false && (trim($value) == '' || is_null($value))) {
                throw new \Exception('Required Value not present item '. $item);
            } else {
                $itemType = str_replace('?', '', $itemType);
            } //Field is required

            // Will mutate original value
            if ($itemType == 'date') {
                $value = date('Y-m-d H:i:s', strtotime($value));
            } elseif ($itemType == 'bool') {
                $value = strtoupper($value) === 'TRUE' || $value === '1'? true : false;
            } else {
                settype($value, $itemType);
            }

            $item = array_key_exists('attr_name', $hashTable) ? $hashTable['attr_name'] : $item;
            
            $model->setAttribute($item, $value);
            return $model;
        }, $modelInstance);

        return $modelWithAttributes;
    }
    /*
    Builder method will return instance with hashTable
    hashtable will be filtered to only use clazz rules and values
    */
    public function usingHashTable(array $hashTable)
    {
        $this->hashTable = $hashTable;
        return $this;
    }
    /*
    Builder method, will return instance applying extra filter to 
    table, allowing to devide one builder in two builders
    */
    public function partitionBy($condition)
    {
        $fields = $this->extractFields();

        $appliesCondition = array_filter($this->hashTable, function ($v, $k) use ($condition) {
            return $condition($k, $v);
        }, ARRAY_FILTER_USE_BOTH);
        
        yield self::createInserterForClass($this->clazzName)
            ->applyingBeforeSaveHook($this->hookFunction)
            ->usingHashTable($appliesCondition);
        
        $notApply = array_filter($this->hashTable, function ($v, $k) use ($condition) {
            return !$condition($k, $v);
        }, ARRAY_FILTER_USE_BOTH);

        yield self::createInserterForClass($this->clazzName)
            ->applyingBeforeSaveHook($this->hookFunction)
            ->usingHashTable($notApply);
    }
    /*
    Builder method will return instance of self attaching a before hook Clojure
    */
    public function applyingBeforeSaveHook($funcClojure)
    {
        $this->hookFunction = $funcClojure;
        return $this;
    }
    /*
    Runs the builder instance returning a saved model
    according to rules in hash table
    */
    public function buildAndSave()
    {
        $modelInstance = $this->insertValuesForFields();
        if (!is_null($this->hookFunction)) {
            $hook = $this->hookFunction;
            $modelInstance = $hook($modelInstance);
        }
       
        $modelInstance->save();

        return $modelInstance;
    }
    /*
    Simple filter to extract rules for Model class passed in constructor
    */
    private function extractFields()
    {
        $className = $this->clazzName;
        $fields = array_filter($this->hashTable, function ($v, $k) use ($className) {
               return (is_array($v) && in_array($className, $v['tables']));
        }, ARRAY_FILTER_USE_BOTH);

        return $fields;
    }
}
