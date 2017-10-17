<?php

namespace Tests\Unit;

use Mockery;
use org\bovigo\vfs\vfsStream;
use App\Http\Services\ImportCsv\ImportCsvService;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ImportCsvServiceTest extends \TestCase
{
    use DatabaseMigrations;

    const CSV_HEADER = "# Years in Sport,Address,Address line 2,Birth Certificate,Cell Phone,City,Country,Current Grade,Date of Birth,Email,First Name,Game Type,Gender,Height,High School Grad Year,Instagram handle,Last Name,League,Level,Middle Name,Organization,Org State,Other Sports Played,Parent / Guardian 1 Cell Phone,Parent / Guardian 1 Email,Parent / Guardian 1 First Name,Parent / Guardian 1 Home Phone,Parent / Guardian 1 Last Name,Parent / Guardian 1 Work Phone,Parent / Guardian 2 Cell Phone,Parent / Guardian 2 Email,Parent / Guardian 2 First Name,Parent / Guardian 2 Home Phone,Parent / Guardian 2 Last Name,Parent / Guardian 2 Work Phone,Photo,SalesForce ID,USSF_ID,Position,Profile Last Updated,School Attending,School District,School State,Season,State,Team,Team Grade,Team Gender,Team Age Group,Twitter Handle,USAFB Right to Market,Weight,Zip";

    const CSV_NEWLINE = "\n";

    const CSV_ROWS = "1,44 summit rd,,,12342314,staten island,USA,K-5,9/1/1998,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,5 foot 10 inches,2018,,FABI,A,YOUTH,sdfsd,OrgName,NY,\"Basketball, Baseball, Soccer, LaCross, Swimming, Volleyball, Softball,  Hockey, Tennis, Golf, Rugby, Other\",,,,,,,,,,,,,,,,,,,,,2017,NY,,,,,,,145 pounds,10307";

    const CSV = self::CSV_HEADER . self::CSV_NEWLINE . self::CSV_ROWS;

    /**
    * Should test that service returns array
    */
    public function testShouldReturnArray() {
        $structure = [
            'csv' => [
                'input.csv' => self::CSV
            ]
        ];
        $root = vfsStream::setup('root', null, $structure);

        $this->assertTrue($root->hasChild('csv/input.csv'));

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYER');
        $this->assertTrue(is_array($response));
    }

    /**
    * Should test a successfull process
    */
    public function testShouldReturnOneProcesedCeroErrors()
    {
        $structure = [
            'csv' => [
                'input.csv' => self::CSV
            ]
        ];
        $root = vfsStream::setup('root', null, $structure);

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYER');
        $this->assertEquals($response['processed'], 1);
        $this->assertEquals($response['errors'], 0);
    }

    /**
    * Should test that required fields are taken into account
    */
    public function testShouldReturnOneErrorIfRequiredFieldIsNotPresent()
    {
        $structure = [
            'csv' => [
                'input.csv' => str_replace('fabval@hotmail.com', '', self::CSV)

            ]
        ];
        $root = vfsStream::setup('root', null, $structure);

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYER');

        $this->assertEquals($response['processed'], 0);
        $this->assertEquals($response['errors'], 1);
    }

    /**
    * Should test that the line has the appropiate number of fields
    */
    public function testShouldReturnOneErrorIfLineIsInconsistent()
    {
        $structure = [
            'csv' => [
                'input.csv' => self::CSV_HEADER . ', OneMoreColumn' . self::CSV_NEWLINE . self::CSV_ROWS . ', OneMoreField'
            ]
        ];

        $root = vfsStream::setup('root', null, $structure);

        $this->assertTrue($root->hasChild('csv/input.csv'));

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYER');
        $this->assertEquals($response['processed'], 0);
        $this->assertEquals($response['errors'], 1);
    }

    /**
    * Should test that process returns error if UsaFbId is present
    */
    public function testShouldFailIfUsaFbIdIsPresent()
    {
        $structureWithUsaFbIdPresent = [
            'csv' => [
                'input.csv' => self::CSV_HEADER . ', usafb_id' . self::CSV_NEWLINE . self::CSV_ROWS . ', USFB_ID_VALUE'
            ]
        ];

        $root = vfsStream::setup('root', null, $structureWithUsaFbIdPresent);

        $this->assertTrue($root->hasChild('csv/input.csv'));

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYER');

        $this->assertEquals($response['processed'], 0);
        $this->assertEquals($response['errors'], 1);
    }

    /**
    * Should process csv regardless of the coulumn order
    */
    public function testShouldReturnOneProcesedCeroErrorsIfColumnsAreInDifferentOrder()
    {
        $reversedCsv = '"'.implode('","', array_reverse(str_getcsv(self::CSV_HEADER, ',', '"'))).'"' .
                       self::CSV_NEWLINE .
                       '"'.implode('","', array_reverse(str_getcsv(self::CSV_ROWS, ',', '"'))).'"';
        $structure = [
            'csv' => [
                'input.csv' => $reversedCsv

            ]
        ];
        $root = vfsStream::setup('root', null, $structure);

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYER');
        $this->assertEquals($response['processed'], 1);
        $this->assertEquals($response['errors'], 0);
    }

    /**
    * Should fail and process nothing, if missing column or wrongly named
    */
    public function testShouldReturnCeroProcessedOneError()
    {
        $header = explode(',', self::CSV_HEADER);
        unset($header[0]);
        $row = explode(',', self::CSV_ROWS);
        unset($row[0]);

        $structure = [
            'csv' => [
                'input.csv' => implode(',',  $header) .
                               self::CSV_NEWLINE .
                               implode(',', $row)

            ]
        ];
        $root = vfsStream::setup('root', null, $structure);

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYER');
        $this->assertEquals($response['processed'], 0);
        $this->assertEquals($response['errors'], 1);
    }

}
