<?php
namespace Tests\Unit;

use Mockery;
use App\Http\Services\ImportCsv\ImportCsvUtils;
use App\Models\Registrant;

use Datetime;

class ImportCsvUtilsTest extends \TestCase
{
    /**
    * Should test filter
    */
    public function testShouldReturnOnlyElementsContainingTablePassedAsParam()
    {   
        $rulesArrayExample = array(
             'type' =>
                array('value' => 'any', 'tables' => array('App\Models\Registrant')),
             'other' =>
                array('value' => 'other', 'tables' => array('App\Models\Other')),
        );
        $result = ImportCsvUtils::filterModel($rulesArrayExample, 'App\Models\Registrant');
        $this->assertEquals(count($result), 1);
    }
    
    /**
    * Should test that if the `table` key is missing it just ignores it
    */
    public function testShouldNotFailIfTableKeyIsMissing()
    {   
        $rulesArrayExample = array(
             'type' =>
                array('value' => 'any'),
             'other' =>
                array('value' => 'other'),
        );
        $result = ImportCsvUtils::filterModel($rulesArrayExample, 'App\Models\Registrant');
        $this->assertEquals(count($result), 0);
    }

    /**
    * Should test that instance reducer works
    */
    public function testShouldReduceArrayToModelInstanceWithAttributes()
    {
        
        
        $rulesArrayExample = array(
             'type' => 2,
             'other' => 5
        );
        $result = ImportCsvUtils::reduceKeyValueToModel($rulesArrayExample, new Registrant);
        
        $this->assertEquals($result->type, $rulesArrayExample['type']);
        $this->assertEquals($result->other, $rulesArrayExample['other']);
    }

    /**
    * Should test that that testRequired throughs exception if value not present
    */
    public function testShouldThroghExceptionIfValueIsEmpty()
    {
        $testValue = '';
        $this->setExpectedException('\Exception');

        $result = ImportCsvUtils::testRequired($testValue);
        
        $this->assertEquals($result, $testValue);
    }
    /**
    * Should test that that testRequired throughs exception if value not present
    */    
    public function testShouldThroughExceptionIfValueIsNull()
    {
        $testValue = null;
        $this->setExpectedException('\Exception');
        
        $result = ImportCsvUtils::testRequired($testValue);
    }

    /**
    * Should test that that testNotRequired throughs exception if value is present
    */
    public function testShouldThroughExceptionIfValueIsPresent()
    {
        $testValue = 'notNullValue';
        $this->setExpectedException('\Exception');
        
        $result = ImportCsvUtils::testNotRequired($testValue);
    }

    /**
    * Should test that that testNotRequired returns null if not present
    */
    public function testShouldReturnNullIfValueIsNotPresent()
    {
        $testValue = null;
        
        $result = ImportCsvUtils::testNotRequired($testValue);
        $this->assertTrue(is_null($result));
    }

    /**
    * Should test correct parsing of date
    */
    public function testShouldParseStringToDate()
    {
        $dateFormat = 'Y-m-d';
        $testValue = '5/29/17';
        $result = ImportCsvUtils::parseToDate($testValue);
        
        $d = DateTime::createFromFormat($dateFormat, $result);
        $this->assertTrue($d && $d->format($dateFormat));
    }
    
    /**
    * Should test incorrect parsing of date
    */
    public function testShouldThroughExceptionIfDateIsWrongFormat()
    {
        $testValue = 'thisAintADate';
        $this->setExpectedException('\Exception');
        
        $result = ImportCsvUtils::parseToDate($testValue);
        
    }

    /**
    * Should convert isRequiredMethod to Clojure
    *
    **/
    public function testShouldConvertTestRequiredMethodToClojure()
    {
        $testValue = 'A cool value';
        $isRequiredClojure = ImportCsvUtils::toClojure('testRequired');
        
        $this->assertTrue(is_callable($isRequiredClojure));
        $response = $isRequiredClojure($testValue);
        
        $this->assertEquals($response, $testValue);
    }

    public function testShouldConvertAnArrayOfRulesToKeyValues()
    {
        $testFunc = function($val) {
            return $val + 1;
        };
        
        $indexMapper = array(
            'Bla' => 0,
            'Bo' => 1
        );

        $valueMapper = [1,2];

        $testRules = array('Bla' => array('rule' => $testFunc, 'field_name' => 'last_name', 'tables' => array('App\Models\Registrant')),
                            'Bo' => array('rule' => $testFunc, 'field_name' => 'level_of_play', 'tables' => array('App\Models\Registrant')));
        
        $result = ImportCsvUtils::mapRulesToArrayOfKeyValue($testRules, $indexMapper, $valueMapper);

        $expected = $testFunc($valueMapper[0]);
        $expected_2 = $testFunc($valueMapper[1]);

        $this->assertEquals($expected, $result['last_name']);
        $this->assertEquals($expected_2, $result['level_of_play']);
        
    }

    public function testShouldLowerCaseAndTurnSpacesIntoUnderscore()
    {
        $testValue = 'This IS A TESt';
        $result = ImportCsvUtils::lowerCaseAndSpacesToUnderscore($testValue);
        $this->assertEquals($result, 'this_is_a_test');
    }

    public function testShouldReturnAnArrayOfFieldNamesWithoutSpacesAndLowerCased()
    {
        $testColumns = array('column 1','cOLumn 2');

        $result = ImportCsvUtils::columnToIndexMapper($testColumns);
        $this->assertEquals($result['column_1'], 0);
        $this->assertEquals($result['column_2'], 1);

    }

    public function testShouldReturnValueIfKeyExists()
    {
        $testValue = 3;
        $testKey = 'key';
        $testArray = array($testKey => $testValue);
        
        $result = ImportCsvUtils::retrieveValueUsingMapper($testArray, $testKey);

         $this->assertEquals($result, $testValue);

    }

    public function testShouldReturnNullIfKeyDoesNotExists()
    {
        $testValue = 3;
        $testKey = 'key';
        $testArray = array('otherKey' => $testValue);
        
        $result = ImportCsvUtils::retrieveValueUsingMapper($testArray, $testKey);

         $this->assertNull($result);

    }
    
}