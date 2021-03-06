<?php

namespace validators\auth;

use ApiTester;
use utils\CommonUtils;


class LoginValidator
{
    /**
     * Function to validate actual response with excepted response
     * @param $actualResponse
     * @param $expectedResponse
     * @param ApiTester $I
     * @param CommonUtils $common
     */
    public function validateResponse($actualResponse, $expectedResponse, ApiTester $I, CommonUtils $common)
    {
        $getActualResObj = $common->convertJsonToArray($actualResponse);
        $getExcepctedResObj = $common->convertJsonToArray($expectedResponse);
        $I->assertEquals($getActualResObj, $getExcepctedResObj);
    }

    /**
     * Function to validate actual success response with excepted success response
     * @param $actualResponse
     * @param $expectedResponse
     * @param ApiTester $I
     * @param CommonUtils $common
     */
    public function validateSuccResponse($actualResponse, $expectedResponse, ApiTester $I, CommonUtils $common)
    {
        $actualArrayList = $common->getArrayOfValue($common->convertJsonToArray($actualResponse));
        $expectedArrayList = $common->getArrayOfValue($common->convertJsonToArray($expectedResponse));
        $common->assertObjects($actualArrayList, $expectedArrayList, $I);
    }

}
