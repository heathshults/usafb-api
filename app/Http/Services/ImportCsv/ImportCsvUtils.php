<?php

namespace App\Http\Services\ImportCsv;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\FunctionalHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ImportCsvUtils
{
    const DATE_FORMAT = 'Y-m-d';
    const EXPECTED_LINE_AMOUNT = 52;
    const RULE_IDX = 'rule';
    const FIELD_NAME_IDX = 'field_name';

    /**
     * Validate the max rows of the csv
     * @param File $file The csv file
     * @return int the number of rows
     */
    public static function countRows($file)
    {
        $fp = file($file);

        return count($fp);
    }

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
            throw new BadRequestHttpException('Required Value not present for item ');
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
            throw new BadRequestHttpException('Value is present, and expected empty');
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
            throw new BadRequestHttpException('Cant parse that date '.$value);
        }
    }

    /**
     *   Will return pased value to boolean
     *   @param $value? as read from csv file
     *   @return Boolean parsed string
     */
    public static function parseToBoolean($value)
    {
        if (strtoupper($value) === 'YES' || $value === '1') {
            return true;
        } elseif (strtoupper($value) === 'NO' || $value === '0' || $value === '') {
            return false;
        } else {
            throw new BadRequestHttpException('Cant parse that string to boolean '.$value);
        }
    }

    /**
     * Will return an instance populated with model values
     * @param array $rules An array of rules with values
     * @param Model $modelInstance holding model to fill with props
     * @return Model with attributes in it
    */
    public static function reduceKeyValueToModel(array $fields, Model $modelInstance)
    {
        $fieldKeys = array_keys($fields);
        return array_reduce($fieldKeys, function ($modelInstance, $item) use ($fields) {
            $value = $fields[$item];
            $modelInstance->setAttribute($item, $value);
            return $modelInstance;
        }, $modelInstance);
    }

    /**
    * Will convert a rules array to a key value array
    * @param array $rules An array of rules
    * @param array $indexMappings an array of index mappings
    * @param array $valueMappings an array of calue mappings
    * @return array of key value
    */
    public static function mapRulesToArrayOfKeyValue(array $rules, array $indexMappings, array $valueMappings)
    {
        
        $indexMapper = FunctionalHelper::curry2(self::toClojure('retrieveValueUsingMapper'), $indexMappings);
        $valueMapper = FunctionalHelper::curry2(self::toClojure('retrieveValueUsingMapper'), $valueMappings);

        return array_reduce(array_keys($rules), function ($accum, $key) use ($rules, $indexMapper, $valueMapper) {
                $rule = $rules[$key][self::RULE_IDX];
                $actual_key = $rules[$key][self::FIELD_NAME_IDX];
                $valueFunction = FunctionalHelper::compose($indexMapper, $valueMapper, $rule);

                $accum[$actual_key] = $valueFunction($key);
                return $accum;
        }, array());
    }

    /**
    * Will return an array where key is the column key and value is the index for that key
    * @param array $columnNames Array of column names
    * @return array of index and column names
    */
    public static function columnToIndexMapper(array $columnNames)
    {
        return array_flip(
            array_map(self::toClojure('lowerCaseAndSpacesToUnderscore'), $columnNames)
        );
    }

    /**
    * Will return true if line is an array and has expected length false otherwise
    * @param mixed $line will be tested for array an length
    * @param number expected column lenght
    * @return bool
    */
    public static function isLineAsExpected($line, $expectedLineAmount = self::EXPECTED_LINE_AMOUNT)
    {
        return is_array($line) && (sizeof($line) == $expectedLineAmount);
    }
    
    /**
    * Will return value if key exists. null otherwise
    * @param array $mappings array to test for key
    * @return bool
    */
    public static function retrieveValueUsingMapper(array $mappings, $key)
    {
        return array_key_exists($key, $mappings) ? $mappings[$key] : null;
    }

    /**
    * Will return the anonimous function of passed method name
    * @param string $methodName method name within this class
    * @return anonimous function
    */
    public static function toClojure($methodName)
    {
        
        return FunctionalHelper::toClojure('App\Http\Services\ImportCsv\ImportCsvUtils', $methodName);
    }
    /**
    * Composes two functions. will lowercase $value and replace spaces for underscores
    * @param string $value value to convert
    * @return new value without spaces and lower cased
    */
    public static function lowerCaseAndSpacesToUnderscore($value)
    {
        return str_replace(' ', '_', strtolower($value));
    }
}
