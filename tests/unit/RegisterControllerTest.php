<?php
namespace Tests\Unit;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\Helpers\AuthMockHelper;

class RegisterControllerTest extends \TestCase
{
    use DatabaseMigrations;
    
    const PLAYER_REQUEST = '{
      "player": {
        "first_name": "My First Name",
        "middle_name": "My mid name",
        "last_name": "My last name",
        "birth_date": "10/26/1983",
        "gender": "male",
        "height": 0,
        "weight": 0,
        "address_first_line": "My address...",
        "address_second_line": "",
        "city": "City",
        "state": "State",
        "zip_code": "1261",
        "country": "US",
        "email": "myemail@guest.com",
        "phone_number": "12345789",
        "game_type": [
          "Youth Flag"
        ],
        "level": "1",
        "grade": "1",
        "graduation_year": 0,
        "sports": "sports 1, sports2",
        "years_at_sport": 0,
        "instgram": "",
        "twitter": "",
        "guardians": [
          {
            "first_name": "g1 first name",
            "last_name": "g1 last name",
            "home_phone": "g1 home phone",
            "work_phone": "g1 work phone",
            "mobile_phone": "g1 mobile phone",
            "email": "g1@gmail.com"
          }
        ],
        "external_id": "noidea"
      },
      "registrations": [
        {
          "league": "theleague",
          "team_name": "superteam!",
          "team_gender": "male",
          "org_name": "org name...",
          "org_state": "org st..",
          "season": "Q12017",
          "right_to_market": true,
          "school_name": "SchoolName",
          "school_district": "Shooldistrict",
          "school_state": "SchoolST",
          "position": "Theposition",
          "external_id": "noidea"
        }
      ]
    }';

    /**
    * Should test file was uploaded successfuly
    */
    public function testSuccessfulRegisterPlayer()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());

        $response = $this->json('POST', '/register/player', json_decode(self::PLAYER_REQUEST, TRUE))
            ->seeJsonStructure(
                [
                    'usafb_id'
                ]
            )->seeStatusCode(200);
    }

    /**
    * Should test json player missing error
    */
    public function testErrorRegisterPlayerMissingPlayer()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());

        $playerRequest = json_decode(self::PLAYER_REQUEST, TRUE);
        unset($playerRequest['player']);
        $response = $this->json('POST', '/register/player', $playerRequest)
            ->seeJsonStructure(
                [
                    'errors'
                ]
            )->seeStatusCode(400);
    }

    /**
    * Should test json registrations missing error
    */
    public function testErrorRegisterPlayerMissingRegistrations()
    {
        $this->app->instance('Auth', AuthMockHelper::authServiceMock());
        $this->app->instance('App\Http\Middleware\Authenticate', AuthMockHelper::authenticateMiddlewareMock());

        $playerRequest = json_decode(self::PLAYER_REQUEST, TRUE);
        unset($playerRequest['registrations']);
        $response = $this->json('POST', '/register/player', $playerRequest)
            ->seeJsonStructure(
                [
                    'errors'
                ]
            )->seeStatusCode(400);
    }
}
