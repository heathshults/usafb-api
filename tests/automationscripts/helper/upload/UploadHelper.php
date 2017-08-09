<?php

namespace helper\upload;

use ApiTester;
use Codeception\Module\REST;

class UploadHelper
{

    /**
     * @param ApiTester $I
     * @param $url
     * @param $token
     * @return string
     */
    public function uploadCall(ApiTester $I, $url, $token,$fileName)
    {
        $I->clearHeaders();
        $I->amBearerAuthenticated($token);

        $files = [
            'csv_file' =>codecept_data_dir($fileName)
        ];

        $I->sendPOST($url, ['csv_file'=>'file'],$files);
        return $I->grabResponse();
    }
}
