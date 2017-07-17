<?php

namespace helper\auth;

use ApiTester;

class LoginHelper
{

    // Reusable method for Login Post Call
    // To be called for other tests

    public function postLoginCall(ApiTester $I, $url, $postBody)
    {

        $I->setHeaders();
        $I->sendPOST($url, $postBody);
        return $I->grabResponse();

    }

}
