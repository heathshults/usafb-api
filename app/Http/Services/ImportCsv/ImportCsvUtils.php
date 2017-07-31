<?php

namespace App\Http\Services\ImportCsv;

use Illuminate\Database\Eloquent\Model;

class ImportCsvUtils
{
    const DATE_FORMAT = 'Y-m-d';
    /**
     *   Will return the array of rules for the given model filtering the others out
     *   @param array $table Rules for the InserterBuilder
     *   @param string $modelName Full Model name
     *   @return array of Rules filtered
     */
    public static function filterModel(array $table, $modelName)
    {
        return array_filter($table, function ($v, $k) use ($modelName) {
               return (is_array($v) && array_key_exists('tables', $v) && in_array($modelName, $v['tables']));
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     *   Will return the value if a value is present.
     *   If value is not present it will through an exception
     *   @param $value value to test if null or empty
     *   @return passed value
    */
    public static function testRequired($value)
    {
        if ((trim($value) == '' || is_null($value))) {
            throw new \Exception('Required Value not present for item ');
        } else {
            return $value;
        }
    }
    /**
     *   Will through an Exception if $value is present
     *   @param $value a value
     *   @return null
    */
    public static function testNotRequired($value)
    {
        if ((trim($value) == '' || is_null($value))) {
            return null;
        } else {
            throw new \Exception('Value is present, and expected empty');
        }
    }

    /**
     *   Will return pased value to date
     *   @param $value? as read from csv file
     *   @return Date parsed date
     */
    public static function parseToDate($value)
    {
        $parsedDate = strtotime($value);
        if ($parsedDate) {
            return date(self::DATE_FORMAT, strtotime($value));
        } else {
            throw new \Exception('Cant parse that date '.$value);
        }
    }

    /**
     *  Will return an instance populated with model values
     *   @param array $rules An array of rules with values
    */
    public static function reduceRulesToModel(array $rules, Model $modelInstance)
    {
        $fields = $rules;
        $fieldKeys = array_keys($fields);
        return array_reduce($fieldKeys, function ($modelInstance, $item) use ($fields) {
            $hashTable = $fields[$item];
            $value = $hashTable['value'];
            
            $attributeName = array_key_exists('attr_name', $hashTable) ? $hashTable['attr_name'] : $item;
            
            $modelInstance->setAttribute($attributeName, $value);
            return $modelInstance;
        }, $modelInstance);
    }
}
