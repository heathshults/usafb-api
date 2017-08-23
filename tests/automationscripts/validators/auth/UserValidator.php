<?php

namespace validators\auth;

use ApiTester;
use utils\CommonUtils;


class UserValidator
{
    /**
     * Function to validate actual user profile with expected user profile
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

    /**
     * Function to validate actual response with excepted response
     * @param $actualResponse
     * @param $expectedResponse
     * @param ApiTester $I
     * @param CommonUtils $common
     */
    public function validateResponse($actualResponse, $expectedResponse, ApiTester $I, CommonUtils $common)
    {
        $actualResponseArr = $common->convertJsonToArray($actualResponse);
        $expectedResponseArr = $common->convertJsonToArray($expectedResponse);
        $common->assertObjects($actualResponseArr, $expectedResponseArr, $I);
    }

    /**
     * Function to assert expected key value not equal to actual key value
     * @param $actualResponse
     * @param $key
     * @param $expectedVal
     * @param ApiTester $I
     * @param CommonUtils $common
     */
    public function validateKeyNotEquals($actualResponse,$key,$expectedVal, ApiTester $I, CommonUtils $common)
    {
        $actualResponseArr = $common->convertJsonToArray($actualResponse);
        $common->assertNotEqualsKey($actualResponseArr,$key,$expectedVal,$I);
    }

    /**
     * Function to assert expected key value  equal to actual key value
     * @param $actualResponse
     * @param $key
     * @param $expectedVal
     * @param ApiTester $I
     * @param CommonUtils $common
     */
    public function validateKeyEquals($actualResponse,$key,$expectedVal, ApiTester $I, CommonUtils $common)
    {
        $actualResponseArr = $common->convertJsonToArray($actualResponse);
        $common->assertEqualsKey($actualResponseArr,$key,$expectedVal,$I);
    }
}
