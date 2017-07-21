<?php

namespace helper\auth;

use ApiTester;

class LoginHelper
{

    /**
     * @param ApiTester $I
     * @param $url
     * @param $postBody
     * @return string
     *
     */
    public function postCall(ApiTester $I, $url, $postBody)
    {
        $I->clearHeaders();
        $I->setHeaders();
        $I->sendPOST($url, $postBody);
        return $I->grabResponse();
    }



}
