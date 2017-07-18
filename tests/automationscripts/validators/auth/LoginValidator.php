<?php

namespace validators\auth;

use ApiTester;
use utils\CommonUtils;


class LoginValidator
{
    /*
     * Validator for ErrorResponse of Login
     */

    public function validateErrResponse($actualResponse, $expectedResponse, ApiTester $I, CommonUtils $common)
    {

        $getActualResObj = $common->convertJsonToArray($actualResponse);
        $getExcepctedResObj = $common->convertJsonToArray($expectedResponse);

        $I->assertEquals($getActualResObj, $getExcepctedResObj);

    }


    /*
    *Validator for SuccessResponse of Login
    */
    public function validateSuccResponse($actualResponse, $expectedResponse, ApiTester $I, CommonUtils $common)
    {

        $actualArrayList = $common->getArrayOfValue($common->convertJsonToArray($actualResponse));
        $expectedArrayList = $common->getArrayOfValue($common->convertJsonToArray($expectedResponse));

        $common->assertObjects($actualArrayList, $expectedArrayList,$I);

    }

}
