<?php

namespace helper\export;

use ApiTester;
use Codeception\Module\REST;

class ExportHelper
{

    /**
     * Function for export get call
     * @param ApiTester $I
     * @param $url
     * @param $token
     * @return string
     */
    public function exportCall(ApiTester $I, $url, $token)
    {
        $I->clearHeaders();
        $I->amBearerAuthenticated($token);
        $I->sendGET($url);
        return $I->grabResponse();
    }
}
