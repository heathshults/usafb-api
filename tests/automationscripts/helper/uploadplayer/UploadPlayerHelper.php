<?php

namespace helper\uploadplayer;

use ApiTester;
use Codeception\Module\REST;

class UploadPlayerHelper
{

    /**
     * @param ApiTester $I
     * @param $url
     * @param $token
     * @return string
     */
    public function uploadPlayerCall(ApiTester $I, $url, $token,$fileName)
    {
        $I->clearHeaders();
        $I->amBearerAuthenticated($token);
        $files = [
            'csv_file' =>codecept_data_dir('/uploadplayer/'.$fileName)
        ];
        $I->sendPOST($url, ['csv_file'=>'file'],$files);
        return $I->grabResponse();
    }
}
