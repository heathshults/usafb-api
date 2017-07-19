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
        @param String: Containg full namespace class name
        @return new instance
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
        // Array<Rules>:: => $this->clazzName::Model with attributes
        $modelWithAttributes = array_reduce($fieldKeys, function ($model, $item) use ($fields) {
            $hashTable = $fields[$item];
            $itemType = $hashTable['type'];
            $value = $hashTable['value'];
            
            $parsedValue = $this->parseToSpecifiedType($item, $value, $itemType);
            $attributeName = array_key_exists('attr_name', $hashTable) ? $hashTable['attr_name'] : $item;
            
            $model->setAttribute($attributeName, $parsedValue);
            return $model;
        }, $modelInstance);

        return $modelWithAttributes;
    }

    /*
     Will parse the type field in our rules array. Returning value
     Will through exception =>
     If type definition contains a '?' then that field is allowed to be null or empty. 
     If it doesnt then it should contain a value.
     Or parsing value accoring to rule cant be done
     @param $item : String Item name 
     @param $value : Value as read from csv file
     @param $expectedType: Type set in rules array the value should be parsed to
    */
    private function parseToSpecifiedType($item, $value, $expectedType)
    {
        if (strpos($expectedType, '?') == false && (trim($value) == '' || is_null($value))) {
            throw new \Exception('Required Value not present for item '. $item);
        } else {
            $type = str_replace('?', '', $expectedType);
        }

        // Will mutate original value
        if ($type == 'date') {
            $value = $this->parseToDate($value);
        } elseif ($type == 'bool') {
            $value = $this->parseToBoolean($value);
        } else {
            settype($value, $type);
        }

        return $value;
    }

    /*
     Will return pased value to date
    @param $value? as read from csv file
    @return Date parsed date
    */
    private function parseToDate($value)
    {
        $parsedDate = strtotime($value);
        if ($parsedDate) {
            return date('Y-m-d H:i:s', strtotime($value));
        } else {
            throw new \Exception('Cant parse that date '.$value);
        }
    }

    /*
     Will return pased value converted to boolean
     @param $value? as read from csv file
     @return Boolean parsed value
    */
    private function parseToBoolean($value)
    {
        return (strtoupper($value) == 'TRUE' || $value == '1');
    }


    /*
    Builder method will return instance with hashTable
    hashtable will be filtered to only use clazz rules and values
    @param $hashTable An array with parsing rules
    @return self : instance
    */
    public function usingHashTable(array $hashTable)
    {
        $this->hashTable = $hashTable;
        return $this;
    }
    /*
    Builder method, will return instance applying extra filter to 
    table, allowing to devide one builder in two builders
    @param Function a function that returns a boolean
    @return Generator function yielding the two sides of the condition for every yield
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
    @param Function a function that returns a Model
    @return self : instance
    */
    public function applyingBeforeSaveHook($funcClojure)
    {
        $this->hookFunction = $funcClojure;
        return $this;
    }

     /*
    Runs the builder instance returning a saved model
    according to rules in hash table
    @return Model
    */
    public function build()
    {
        $modelInstance = $this->insertValuesForFields();
        if (!is_null($this->hookFunction)) {
            $hook = $this->hookFunction;
            $modelInstance = $hook($modelInstance);
        }
        return $modelInstance;
    }

    /*
    Runs the builder instance returning a saved model
    according to rules in hash table
    @return Model
    */
    public function buildAndSave()
    {
        $modelInstance = $this->build();
       
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
