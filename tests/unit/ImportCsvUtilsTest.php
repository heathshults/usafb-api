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
             'type' =>
                array('value' => 'any', 'tables' => array('App\Models\Registrant')),
             'other' =>
                array('value' => 'other', 'tables' => array('App\Models\Registrant'))
        );
        $result = ImportCsvUtils::reduceRulesToModel($rulesArrayExample, new Registrant);
        
        $this->assertEquals($result->type, $rulesArrayExample['type']['value']);
        $this->assertEquals($result->other, $rulesArrayExample['other']['value']);
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

    
}