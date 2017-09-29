<?php
namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\Helpers\AuthMockHelper;
use Tests\Helpers\AwsMockHelper;

class UploadControllerTest extends \TestCase
{
    use DatabaseMigrations;

    const PLAYERS_CSV = "# Years in Sport,Address,Address line 2,Birth Certificate,Cell Phone,City,County,Current Grade,Date of Birth,Email,First Name,Game Type,Gender,Height,High School Grad Year,Instagram handle,Last Name,League,Level,Middle Name,Organization,Org State,Other Sports Played,Parent / Guardian 1 Cell Phone,Parent / Guardian 1 Email,Parent / Guardian 1 First Name,Parent / Guardian 1 Home Phone,Parent / Guardian 1 Last Name,Parent / Guardian 1 Work Phone,Parent / Guardian 2 Cell Phone,Parent / Guardian 2 Email,Parent / Guardian 2 First Name,Parent / Guardian 2 Home Phone,Parent / Guardian 2 Last Name,Parent / Guardian 2 Work Phone,Photo,SalesForce ID,USSF_ID,Position,Profile Last Updated,School Attending,School District,School State,Season,State,Team,Team Grade,Team Gender,Team Age Group,Twitter Handle,USAFB Right to Market,Weight,Zip
        1,44 summit rd,,,12342314,staten island,USA,K-5,9/1/1998,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,5 foot 10 inches,2018,,FABI,A,YOUTH,sdfsd,OrgName,NY,\"Basketball, Baseball, Soccer, LaCross, Swimming, Volleyball, Softball,  Hockey, Tennis, Golf, Rugby, Other\",,,,,,,,,,,,,,,,,,,,,2017,NY,,,,,,,145 pounds,10307";

    const COACHES_CSV = "# of Years Coaching,Address,Address line 2,Cell Phone,City,County,Date of Birth,Email,First Name,Game Type,Gender,Last Name,League,Level,Middle Name,Organization,Org State,SalesForce ID,USSF_ID,School,School District,School State,Season,State,Team,Team Gender,USAFB Right to Market,Zip,Certifications,Coach Role
        1,44 summit rd,,12342314,staten island,USA,9/1/2006,fabval@hotmail.com,MARIOLUCA,YOUTH FLAG,Male,FABI,A,YOUTH,sdfsd,OrgName,NY,,,,,,2017,NY,,,,10307,\"Certification1, Certification 2, etc\",\"Head Coach, Quarterback Coach, Wide Reciever Coach, Linebacker Coach, Offensive Coordinator , Special Teams, Assistant Coach, Tight End Coach, Running Back Coach, Defensive Back Coach, Defensive Coordinator\"";

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
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());
        AwsMockHelper::mockAwsFacade();
        $structure = [
            'csv' => [
                'input.csv' => self::PLAYERS_CSV
            ]
        ];

    	$response = $this->call('POST', '/registrants/import?type=player',
            [],
            [],
            ['csv_file' => $this->createCsvUploadFile($structure)]
        );

        $this->assertEquals(200, $response->status());
    }

    /**
    * Should test file was uploaded successfuly
    */
    public function testSuccessfulUploadingCoachCsv()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());
        AwsMockHelper::mockAwsFacade();
        $structure = [
            'csv' => [
                'input.csv' => self::COACHES_CSV
            ]
        ];

        $response = $this->call('POST', '/registrants/import?type=coach',
            [],
            [],
            ['csv_file' => $this->createCsvUploadFile($structure)]
        );

        $this->assertEquals(200, $response->status());
    }
}
