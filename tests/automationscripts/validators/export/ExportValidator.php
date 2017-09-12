<?php

namespace validators\export;

use ApiTester;
use utils\CommonUtils;


class ExportValidator
{

    /**
     * Function to validate actual response with excepted response
     * @param $actualResponse
     * @param $expectedResponse
     * @param ApiTester $I
     * @param CommonUtils $common
     */
    public function validateExportResponse($actualResponse, $expectedResponse, ApiTester $I, CommonUtils $common)
    {
        $actualArrayList = $common->convertJsonToArray($actualResponse);
        $expectedArrayList = $common->convertJsonToArray($expectedResponse);
        $common->assertObjects($actualArrayList, $expectedArrayList, $I);
    }
}
