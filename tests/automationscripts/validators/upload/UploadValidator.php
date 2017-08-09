<?php

namespace validators\upload;

use ApiTester;
use utils\CommonUtils;


class UploadValidator
{


    /**
     * Validate Success Response
     * @param $actualResponse
     * @param $expectedResponse
     * @param ApiTester $I
     * @param CommonUtils $common
     */
    public function validateResponse($actualResponse, $expectedResponse, ApiTester $I, CommonUtils $common)
    {

        $actualArrayList = $common->convertJsonToArray($actualResponse);
        $expectedArrayList = $common->convertJsonToArray($expectedResponse);
        $common->assertObjects($actualArrayList, $expectedArrayList, $I);
    }


}
