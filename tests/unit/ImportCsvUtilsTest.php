<?php
namespace Tests\Unit;

use Mockery;
use App\Http\Services\ImportCsv\ImportCsvUtils;
use App\Models\Registrant;

use Datetime;

class ImportCsvUtilsTest extends \TestCase
{
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

    public function testShouldThroghExceptionIfValueIsEmpty()
    {
        $testValue = '';
        $this->setExpectedException('\Exception');

        $result = ImportCsvUtils::testRequired($testValue);
        
        $this->assertEquals($result, $testValue);
    }
    
    public function testShouldThroughExceptionIfValueIsNull()
    {
        $testValue = null;
        $this->setExpectedException('\Exception');
        
        $result = ImportCsvUtils::testRequired($testValue);
    }

    public function testShouldThroughExceptionIfValueIsPresent()
    {
        $testValue = 'notNullValue';
        $this->setExpectedException('\Exception');
        
        $result = ImportCsvUtils::testNotRequired($testValue);
    }
    
    public function testShouldReturnNullIfValueIsNotPresent()
    {
        $testValue = null;
        
        $result = ImportCsvUtils::testNotRequired($testValue);
        $this->assertTrue(is_null($result));
    }

    public function testShouldParseStringToDate()
    {
        $dateFormat = 'Y-m-d';
        $testValue = '5/29/17';
        $result = ImportCsvUtils::parseToDate($testValue);
        
        $d = DateTime::createFromFormat($dateFormat, $result);
        $this->assertTrue($d && $d->format($dateFormat));
    }

    public function testShouldThroughExceptionIfDateIsWrongFormat()
    {
        $testValue = 'thisAintADate';
        $this->setExpectedException('\Exception');
        
        $result = ImportCsvUtils::parseToDate($testValue);
        
    }

    
}