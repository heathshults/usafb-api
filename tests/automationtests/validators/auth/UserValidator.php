<?php

namespace validators\auth;

use ApiTester;
use utils\CommonUtils;


class UserValidator
{

    // This method is used to validate user profile compare Expected Response vc Actual Response

    public function verifyUserProfile(ApiTester $I, $actualResponse, $expectedResponse, CommonUtils $common)
    {
        $actualResponseArr = $common->convertJsonToArray($actualResponse);
        $common->assertObjects($actualResponseArr, $expectedResponse,$I);

    }
}
