<?php
namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UploadControllerTest extends \TestCase
{
    use DatabaseMigrations;
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
    * Should test file was uploaded successfuly
    */
    public function testSuccessfulUploadingPlayerCsv()
    {
        $structure = [
            'csv' => [
                'input.csv' => "# Years in Sport,Address,Birth Certificate,Cell Phone,City,County,Current Grade,Date of Birth,Email,First Name,Game Type,Gender,Height,High School Grad Year,Instagram handle,Last Name,League,Level of Play,Middle Name,Organization,Org State,Other Sports Played,Parent / Guardian 1 Cell Phone,Parent / Guardian 1 Email,Parent / Guardian 1 First Name,Parent / Guardian 1 Home Phone,Parent / Guardian 1 Last Name,Parent / Guardian 1 Work Phone,Parent / Guardian 2 Cell Phone,Parent / Guardian 2 Email,Parent / Guardian 2 First Name,Parent / Guardian 2 Home Phone,Parent / Guardian 2 Last Name,Parent / Guardian 2 Work Phone,Photo,SalesForce ID,USSF_ID,Position,Profile Last Updated,School Attending,School District,School State,Season,State,Team,Team Grade,Team Gender,Team Age Group,Twitter Handle,USAFB Right to Market,Weight,Player Zip\n".
                                "20,44 summit rd,,(917) 204-3772,staten island,USA,K-12,9/1/06,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,23,1984,,FABI,A,YOUTH,P name, United Sports Youth League,CA,other,(718) 612-2798,fabval@hotmail.com,Valerie,(917) 204-3772,Fabi,,(646) 623-5013,neil.fabi@yahoo.com,Nino,(917) 805-0826,Fabi,,www.yahoo.com,asadasd,,Pos,9/1/16,PS 1,CA,NY,2016-2017,NY,U2 Giants,4th Grade,Coed,,,,34l,10307"

            ]
        ];

        $this->withoutMiddleware();
    	$response = $this->call('POST', '/registrants/import?type=player',
            [],
            [],
            ['csv_file' => $this->createCsvUploadFile($structure)]
        );

        $this->assertEquals(200, $response->status());
    }
    /**
    * Should test EndPoint responds expected json
    */
    public function testShouldReturnAmountOfProcesedAndErrorsPlayerCsv()
    {
        $structure = [
            'csv' => [
                'input.csv' =>"# Years in Sport,Address,Address line 2,Birth Certificate,Cell Phone,City,County,Current Grade,Date of Birth,Email,First Name,Game Type,Gender,Height,High School Grad Year,Instagram handle,Last Name,League,Level of Play,Middle Name,Organization,Org State,Other Sports Played,Parent / Guardian 1 Cell Phone,Parent / Guardian 1 Email,Parent / Guardian 1 First Name,Parent / Guardian 1 Home Phone,Parent / Guardian 1 Last Name,Parent / Guardian 1 Work Phone,Parent / Guardian 2 Cell Phone,Parent / Guardian 2 Email,Parent / Guardian 2 First Name,Parent / Guardian 2 Home Phone,Parent / Guardian 2 Last Name,Parent / Guardian 2 Work Phone,Photo,SalesForce ID,USSF_ID,Position,Profile Last Updated,School Attending,School District,School State,Season,State,Team,Team Grade,Team Gender,Team Age Group,Twitter Handle,USAFB Right to Market,Weight,Zip\n".
                                "20,44 summit rd,,,(917) 204-3772,staten island,USA,K-12,9/1/06,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,23,1984,,FABI,A,YOUTH,P name, United Sports Youth League,CA,other,(718) 612-2798,fabval@hotmail.com,Valerie,(917) 204-3772,Fabi,,(646) 623-5013,neil.fabi@yahoo.com,Nino,(917) 805-0826,Fabi,,www.yahoo.com,asadasd,,Pos,9/1/16,PS 1,CA,NY,2016-2017,NY,U2 Giants,4th Grade,Coed,,,,34l,10307"

            ]
        ];

        $this->withoutMiddleware();
    	$response = $this->call('POST', '/registrants/import?type=player',
            [],
            [],
            ['csv_file' => $this->createCsvUploadFile($structure)]
        );
        
        $this->assertEquals($response->getOriginalContent(), array('processed' => 1, 'errors' => 0));
    }

    /**
    * Should test file was uploaded successfuly
    */
    public function testSuccessfulUploadingCoachCsv()
    {
        $structure = [
            'csv' => [
                'input.csv' =>"# of Years Coaching,Address,Address line 2,Cell Phone,City,County,Date of Birth,Email,First Name,Game Type,Gender,Last Name,League,Level of Play,Middle Name,Organization,Org State,SalesForce ID,USSF_ID,School,School District,School State,Season,State,Team,Team Gender,USAFB Right to Market,Zip,Certifications,Coach Role,Coaching Level
                    1,44 summit rd,,12342314,staten island,USA,9/1/2006,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,FABI,A,YOUTH,sdfsd,OrgName,NY,,,,,,2017,NY,,,,10307,\"Certification1, Certification 2, etc\",\"Head Coach, Quarterback Coach, Wide Reciever Coach, Linebacker Coach, Offensive Coordinator , Special Teams, Assistant Coach, Tight End Coach, Running Back Coach, Defensive Back Coach, Defensive Coordinator\","
            ]
        ];

        $this->withoutMiddleware();
        $response = $this->call('POST', '/registrants/import?type=coach',
            [],
            [],
            ['csv_file' => $this->createCsvUploadFile($structure)]
        );

        $this->assertEquals(200, $response->status());
    }
    /**
    * Should test EndPoint responds expected json
    */
    public function testShouldReturnAmountOfProcesedAndErrorsCoachCsv()
    {
        $structure = [
            'csv' => [
                'input.csv' =>"# of Years Coaching,Address,Address line 2,Cell Phone,City,County,Date of Birth,Email,First Name,Game Type,Gender,Last Name,League,Level of Play,Middle Name,Organization,Org State,SalesForce ID,USSF_ID,School,School District,School State,Season,State,Team,Team Gender,USAFB Right to Market,Zip,Certifications,Coach Role,Coaching Level
                    1,44 summit rd,,12342314,staten island,USA,9/1/2006,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,FABI,A,YOUTH,sdfsd,OrgName,NY,,,,,,2017,NY,,,,10307,\"Certification1, Certification 2, etc\",\"Head Coach, Quarterback Coach, Wide Reciever Coach, Linebacker Coach, Offensive Coordinator , Special Teams, Assistant Coach, Tight End Coach, Running Back Coach, Defensive Back Coach, Defensive Coordinator\","

            ]
        ];

        $this->withoutMiddleware();
        $response = $this->call('POST', '/registrants/import?type=coach',
            [],
            [],
            ['csv_file' => $this->createCsvUploadFile($structure)]
        );
        
        $this->assertEquals($response->getOriginalContent(), array('processed' => 1, 'errors' => 0));
    }
}