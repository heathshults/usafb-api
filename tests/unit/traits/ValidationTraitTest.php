<?php
namespace Tests\Unit;

use Mockery;
use App\Http\Services\ImportCsv\ImportCsvUtils;
use App\Models\Registrant;
use Illuminate\Http\UploadedFile;
use org\bovigo\vfs\vfsStream;
use App\Traits\ValidationTrait;

use Datetime;

class ValidationTraitTest extends \TestCase
{

    /**
    * Should test that that testRequired throughs exception if value not present
    */
    public function testShouldThroghExceptionIfValueIsEmpty()
    {
        $testValue = '';
        $this->setExpectedException('\Exception');

        $result = ValidationTrait::required('item', $testValue);

        $this->assertEquals($result, $testValue);
    }
    /**
    * Should test that that testRequired throughs exception if value not present
    */
    public function testShouldThroughExceptionIfValueIsNull()
    {
        $testValue = null;
        $this->setExpectedException('\Exception');

        $result = ValidationTrait::required('item', $testValue);
    }

    /**
    * Should test that that testNotRequired throughs exception if value is present
    */
    public function testShouldThroughExceptionIfValueIsPresent()
    {
        $testValue = 'notNullValue';
        $this->setExpectedException('\Exception');

        $result = ValidationTrait::requiredEmpty('item', $testValue);
    }

    /**
    * Should test that that testNotRequired returns null if not present
    */
    public function testShouldReturnNullIfValueIsNotPresent()
    {
        $testValue = null;

        $result = ValidationTrait::notRequired('item', $testValue);
        $this->assertTrue(is_null($result));
    }

    /**
    * Should test correct parsing of date
    */
    public function testShouldParseStringToDate()
    {
        $dateFormat = 'Y-m-d';
        $testValue = '5/29/17';
        $result = ValidationTrait::parseToDate('item', $testValue);

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

        $result = ValidationTrait::parseToDate('item', $testValue);

    }

}
