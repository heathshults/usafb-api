<?php

namespace helper\auth;

use ApiTester;

class LoginHelper
{

    /*
     * helper call postLogin
    */

    public function postLogin(ApiTester $I, $url, $postBody)
    {

        $I->setHeaders();
        $I->sendPOST($url, $postBody);
        return $I->grabResponse();

    }

}
