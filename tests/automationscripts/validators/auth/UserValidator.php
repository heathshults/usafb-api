<?php

namespace validators\auth;

use ApiTester;
use utils\CommonUtils;


class UserValidator
{

    /*
      * Validator for user profile scenarios
      */

    public function verifyUserProfile(ApiTester $I, $actualResponse, $expectedResponse, CommonUtils $common)
    {
        $actualResponseArr = $common->convertJsonToArray($actualResponse);
        $expectedResponseArr = $common->convertJsonToArray($expectedResponse);
        $common->assertObjects($actualResponseArr, $expectedResponseArr, $I);

    }


}
