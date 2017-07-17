<?php

namespace helper\auth;

use ApiTester;
use Codeception\Module\REST;

class UserHelper
{

    // Reusable method for user Profile Call
    // To be called for other tests


    public function getUserByToken(ApiTester $I, $url, $token, $key)
    {
        $I->clearHeaders();
        $I->setHeaders();
        if (! ($key == "NoHeader" or  $key == "NoBearer")) {
            $I->amBearerAuthenticated($token);
        }

        if ($key == "NoBearer") {
            $I->clearHeaders();
            $I->setHeaders();
            $I->haveHttpHeader('Authorization', 'Basic');
        }
        $I->sendGET($url);
        return $I->grabResponse();
    }



}
