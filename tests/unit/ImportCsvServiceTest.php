<?php

namespace Tests\Unit;

use Mockery;
use org\bovigo\vfs\vfsStream;
use App\Http\Services\ImportCsv\ImportCsvService;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ImportCsvServiceTest extends \TestCase
{
    use DatabaseMigrations;
    /**
    * Should test that service returns array 
    */
    public function testShouldReturnArray() {
        $structure = [
            'csv' => [
                'input.csv' => "# Years in Sport,Address,Address line 2,Birth Certificate,Cell Phone,City,County,Current Grade,Date of Birth,Email,First Name,Game Type,Gender,Height,High School Grad Year,Instagram handle,Last Name,League,Level of Play,Middle Name,Organization,Org State,Other Sports Played,Parent / Guardian 1 Cell Phone,Parent / Guardian 1 Email,Parent / Guardian 1 First Name,Parent / Guardian 1 Home Phone,Parent / Guardian 1 Last Name,Parent / Guardian 1 Work Phone,Parent / Guardian 2 Cell Phone,Parent / Guardian 2 Email,Parent / Guardian 2 First Name,Parent / Guardian 2 Home Phone,Parent / Guardian 2 Last Name,Parent / Guardian 2 Work Phone,Photo,SalesForce ID,SalesForce ID,Profile Last Updated,School Attending,School District,School State,Season,State,Team,Team Grade,Team Gender,Twitter Handle,USAFB Right to Market,Weight,Player Zip \n".
                                "20,44 summit rd,,,(917) 204-3772,staten island,USA,Some String,9/1/06,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,23,1984,,FABI,A,YOUTH,P name, United Sports Youth League,CA,other,(718) 612-2798,fabval@hotmail.com,Valerie,(917) 204-3772,Fabi,,(646) 623-5013,neil.fabi@yahoo.com,Nino,(917) 805-0826,Fabi,,www.yahoo.com,asadasd,Pos,9/1/16,PS 1,CA,NY,2016-2017,NY,U2 Giants,4th Grade,Coed,,,34l,10307"

            ]
        ];
        $root = vfsStream::setup('root', null, $structure);

        $this->assertTrue($root->hasChild('csv/input.csv'));

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYERS');
        $this->assertTrue(is_array($response));
    }
    
    /**
    * Should test a successfull process 
    */
    public function testShouldReturnOneProcesedCeroErrors()
    {
        $structure = [
            'csv' => [
                'input.csv' => "# Years in Sport,Address,Address line 2,Birth Certificate,Cell Phone,City,County,Current Grade,Date of Birth,Email,First Name,Game Type,Gender,Height,High School Grad Year,Instagram handle,Last Name,League,Level of Play,Middle Name,Organization,Org State,Other Sports Played,Parent / Guardian 1 Cell Phone,Parent / Guardian 1 Email,Parent / Guardian 1 First Name,Parent / Guardian 1 Home Phone,Parent / Guardian 1 Last Name,Parent / Guardian 1 Work Phone,Parent / Guardian 2 Cell Phone,Parent / Guardian 2 Email,Parent / Guardian 2 First Name,Parent / Guardian 2 Home Phone,Parent / Guardian 2 Last Name,Parent / Guardian 2 Work Phone,Photo,SalesForce ID,USSF_ID,Position,Profile Last Updated,School Attending,School District,School State,Season,State,Team,Team Grade,Team Gender,Twitter Handle,USAFB Right to Market,Weight,Zip\n".
                                "20,44 summit rd,,,(917) 204-3772,staten island,USA,Some String,9/1/06,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,23,1984,,FABI,A,YOUTH,P name, United Sports Youth League,CA,other,(718) 612-2798,fabval@hotmail.com,Valerie,(917) 204-3772,Fabi,,(646) 623-5013,neil.fabi@yahoo.com,Nino,(917) 805-0826,Fabi,,www.yahoo.com,asadasd,,Pos,9/1/16,PS 1,CA,NY,2016-2017,NY,U2 Giants,4th Grade,Coed,,,34l,10307"

            ]
        ];
        $root = vfsStream::setup('root', null, $structure);

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYERS');
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
                'input.csv' => "# Years in Sport,Address,Address line 2,Birth Certificate,Cell Phone,City,County,Current Grade,Date of Birth,Email,First Name,Game Type,Gender,Height,High School Grad Year,Instagram handle,Last Name,League,Level of Play,Middle Name,Organization,Org State,Other Sports Played,Parent / Guardian 1 Cell Phone,Parent / Guardian 1 Email,Parent / Guardian 1 First Name,Parent / Guardian 1 Home Phone,Parent / Guardian 1 Last Name,Parent / Guardian 1 Work Phone,Parent / Guardian 2 Cell Phone,Parent / Guardian 2 Email,Parent / Guardian 2 First Name,Parent / Guardian 2 Home Phone,Parent / Guardian 2 Last Name,Parent / Guardian 2 Work Phone,Photo,SalesForce ID,USSF_ID,Position,Profile Last Updated,School Attending,School District,School State,Season,State,Team,Team Grade,Team Gender,Twitter Handle,USAFB Right to Market,Weight,Zip\n".
                                "23,,,,(917) 204-3772,staten island,USA,Some String,9/1/06,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,23,1984,,FABI,A,YOUTH,P name, United Sports Youth League,CA,other,(718) 612-2798,fabval@hotmail.com,Valerie,(917) 204-3772,Fabi,,(646) 623-5013,neil.fabi@yahoo.com,Nino,(917) 805-0826,Fabi,,www.yahoo.com,asadasd,Pos,9/1/16,PS 1,CA,NY,2016-2017,NY,U2 Giants,4th Grade,Coed,,,34l,10307"

            ]
        ];
        $root = vfsStream::setup('root', null, $structure);

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYERS');

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
                'input.csv' => "# Years in Sport,Address,Address line 2,Birth Certificate,Cell Phone,City,County,Current Grade,Date of Birth,Email,First Name,Game Type,Gender,Height,High School Grad Year,Instagram handle,Last Name,League,Level of Play,Middle Name,Organization,Org State,Other Sports Played,Parent / Guardian 1 Cell Phone,Parent / Guardian 1 Email,Parent / Guardian 1 First Name,Parent / Guardian 1 Home Phone,Parent / Guardian 1 Last Name,Parent / Guardian 1 Work Phone,Parent / Guardian 2 Cell Phone,Parent / Guardian 2 Email,Parent / Guardian 2 First Name,Parent / Guardian 2 Home Phone,Parent / Guardian 2 Last Name,Parent / Guardian 2 Work Phone,Photo,SalesForce ID,USSF_ID,Position,Profile Last Updated,School Attending,School District,School State,Season,State,Team,Team Grade,Team Gender,Twitter Handle,USAFB Right to Market,Weight,Zip\n".
                                "MARIOLUCA,YOUTH FLAG,Male,23,1984,,FABI,A,YOUTH,P name, United Sports Youth League,CA,other,(718) 612-2798,fabval@hotmail.com,Valerie,(917) 204-3772,Fabi,,(646) 623-5013,neil.fabi@yahoo.com,Nino,(917) 805-0826,Fabi,,www.yahoo.com,asadasd,Pos,9/1/16,PS 1,CA,NY,2016-2017,NY,U2 Giants,4th Grade,Coed,,,34l,10307"

            ]
        ];

        $root = vfsStream::setup('root', null, $structure);

        $this->assertTrue($root->hasChild('csv/input.csv'));

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYERS');
        $this->assertEquals($response['processed'], 0);
        $this->assertEquals($response['errors'], 1);
    }
    
    /**
    * Should test that process returns error if UssfId is present
    */
    public function testShouldFailIfUssfbIsPresent()
    {
        $structureWithUssfPresent = [
            'csv' => [
                'input.csv' => "# Years in Sport,Address,Address line 2,Birth Certificate,Cell Phone,City,County,Current Grade,Date of Birth,Email,First Name,Game Type,Gender,Height,High School Grad Year,Instagram handle,Last Name,League,Level of Play,Middle Name,Organization,Org State,Other Sports Played,Parent / Guardian 1 Cell Phone,Parent / Guardian 1 Email,Parent / Guardian 1 First Name,Parent / Guardian 1 Home Phone,Parent / Guardian 1 Last Name,Parent / Guardian 1 Work Phone,Parent / Guardian 2 Cell Phone,Parent / Guardian 2 Email,Parent / Guardian 2 First Name,Parent / Guardian 2 Home Phone,Parent / Guardian 2 Last Name,Parent / Guardian 2 Work Phone,Photo,SalesForce ID,USSF_ID,Position,Profile Last Updated,School Attending,School District,School State,Season,State,Team,Team Grade,Team Gender,Twitter Handle,USAFB Right to Market,Weight,Zip\n".
                                "20,44 summit rd,,(917) 204-3772,staten island,USA,Some String,9/1/06,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,23,1984,,FABI,A,YOUTH,P name, United Sports Youth League,CA,other,(718) 612-2798,fabval@hotmail.com,Valerie,(917) 204-3772,Fabi,,(646) 623-5013,neil.fabi@yahoo.com,Nino,(917) 805-0826,Fabi,,www.yahoo.com,asadasd,someId,Pos,9/1/16,PS 1,CA,NY,2016-2017,NY,U2 Giants,4th Grade,Coed,,,34l,10307"

            ]
        ];

        $root = vfsStream::setup('root', null, $structureWithUssfPresent );

        $this->assertTrue($root->hasChild('csv/input.csv'));

        $importService = new ImportCsvService;
        $response = $importService->importCsvFile($root->url().'/csv/input.csv', 'PLAYERS');
        
        $this->assertEquals($response['processed'], 0);
        $this->assertEquals($response['errors'], 1);
    }
    public function testShouldReturnAnArrayOfRules()
    {
        $importService = new ImportCsvService;
        $rules = $importService->getRules();
        foreach($rules as $rule){
            $this->assertTrue(is_callable($rule['rule']));
        }
    }

}