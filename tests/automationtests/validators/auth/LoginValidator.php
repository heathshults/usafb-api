<?php

namespace validators\auth;

use ApiTester;
use utils\CommonUtils;


class LoginValidator
{
    // This method is used to validate error response

    public function validateErrResponse($actualResponse, $expectedResponse, ApiTester $I, CommonUtils $common)
    {

        $getActualResObj = $common->convertJsonToArray($actualResponse);
        $getExcepctedResObj = $common->convertJsonToArray($expectedResponse);

        $I->assertEquals($getActualResObj, $getExcepctedResObj);

    }


    /* This method is used to validate the key and value sent from Auth0

    */

    public function validateSuccResponse($actualResponse, $expectedResponse, ApiTester $I, CommonUtils $common)
    {

        $actualArrayList = $common->getArrayOfValue($common->convertJsonToArray($actualResponse));
        $expectedArrayList = $common->getArrayOfValue($common->convertJsonToArray($expectedResponse));

        $common->assertObjects($actualArrayList, $expectedArrayList,$I);

    }

}
