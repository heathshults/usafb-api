<?php
namespace Tests\Unit;

use Mockery;
use App\Http\Services\ImportCsv\ImportCsvUtils;
use App\Models\Registrant;
use Illuminate\Http\UploadedFile;
use org\bovigo\vfs\vfsStream;

use Datetime;

class ImportCsvUtilsTest extends \TestCase
{
    const CSV = "# Years in Sport,Address,Address line 2,Birth Certificate,Cell Phone,City,Country,Current Grade,Date of Birth,Email,First Name,Game Type,Gender,Height,High School Grad Year,Instagram handle,Last Name,League,Level,Middle Name,Organization,Org State,Other Sports Played,Parent / Guardian 1 Cell Phone,Parent / Guardian 1 Email,Parent / Guardian 1 First Name,Parent / Guardian 1 Home Phone,Parent / Guardian 1 Last Name,Parent / Guardian 1 Work Phone,Parent / Guardian 2 Cell Phone,Parent / Guardian 2 Email,Parent / Guardian 2 First Name,Parent / Guardian 2 Home Phone,Parent / Guardian 2 Last Name,Parent / Guardian 2 Work Phone,Photo,SalesForce ID,USSF_ID,Position,Profile Last Updated,School Attending,School District,School State,Season,State,Team,Team Grade,Team Gender,Team Age Group,Twitter Handle,USAFB Right to Market,Weight,Zip
        1,44 summit rd,,,12342314,staten island,USA,K-5,9/1/2006,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,5 foot 10 inches,2018,,FABI,A,YOUTH,sdfsd,OrgName,NY,\"Basketball, Baseball, Soccer, LaCross, Swimming, Volleyball, Softball,  Hockey, Tennis, Golf, Rugby, Other\",,,,,,,,,,,,,,,,,,,,,2017,NY,,,,,,,145 pounds,10307";

    /**
    * Helper function to mock file for file upload
    */
    protected function createCsvUploadFile($structure)
    {
        $root = vfsStream::setup('root', null, $structure);

        return new UploadedFile(
            $root->url().'/csv/input.csv',
            'input.csv',
            null,
            null,
            null,
            true
        );
    }

    /**
    * Should test the csv number of rows
    */
    public function testShouldReturnNumberOfRows()
    {
        $structure = [
            'csv' => [
                'input.csv' => self::CSV
            ]
        ];

        $file = $this->createCsvUploadFile($structure);
        $count = ImportCsvUtils::countRows($file);

        $this->assertEquals($count, 2);
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
        $this->assertEquals($result[0], 'column_1');
        $this->assertEquals($result[1], 'column_2');

    }

}
