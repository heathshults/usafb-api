<?php

namespace helper\auth;

use ApiTester;
use Codeception\Module\REST;

class UserHelper
{

    /**
     * @param ApiTester $I
     * @param $url
     * @param $token
     * @param $key
     * @return string
     */
    public function getUserByToken(ApiTester $I, $url, $token, $key)
    {
        $I->clearHeaders();
        $I->setHeaders();
        if (!($key == "NoHeader" or $key == "NoBearer")) {
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
