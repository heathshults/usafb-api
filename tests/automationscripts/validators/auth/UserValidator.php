<?php

namespace validators\auth;

use ApiTester;
use utils\CommonUtils;


class UserValidator
{
    /**
     * @param ApiTester $I
     * @param $actualResponse
     * @param $expectedResponse
     * @param CommonUtils $common
     */
    public function verifyUserProfile(ApiTester $I, $actualResponse, $expectedResponse, CommonUtils $common)
    {
        $actualResponseArr = $common->convertJsonToArray($actualResponse);
        $expectedResponseArr = $common->convertJsonToArray($expectedResponse);
        $common->assertObjects($actualResponseArr, $expectedResponseArr, $I);
    }


}
