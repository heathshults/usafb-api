<?php

namespace helper\auth;

use ApiTester;
use Codeception\Module\REST;

class UserHelper
{

    // Reusable method for Login Post Call
    // To be called for other tests



    public function getUserCall(ApiTester $I,$url,$token,$common)
    {
        $I->clearHeaders();
        $I->setHeaders();
        $I->amBearerAuthenticated($token);
        $I->sendGET($url);
        return $I->grabResponse();
    }

}
