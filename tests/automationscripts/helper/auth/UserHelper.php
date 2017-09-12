<?php

namespace helper\auth;

use ApiTester;
use Codeception\Module\REST;

class UserHelper
{

    /**
     * Function for user get call by token
     * @param ApiTester $I
     * @param $url
     * @param $token
     * @param $key
     * @return string
     */
    public function getUserByToken(ApiTester $I, $url, $token, $key)
    {
        $I->clearHeaders();
        $I->setHeaders();
        if (!($key == "NoHeader" or $key == "NoBearer")) {
            $I->amBearerAuthenticated($token);
        }

        if ($key == "NoBearer") {
            $I->clearHeaders();
            $I->setHeaders();
            $I->haveHttpHeader('Authorization', 'Basic');
        }
        $I->sendGET($url);
        return $I->grabResponse();
    }

    /**
     * Function for post call
     * @param ApiTester $I
     * @param $url
     * @param $postBody
     * @param $token
     * @return string
     */
    public function postCall(ApiTester $I, $url, $postBody, $token)
    {
        $I->clearHeaders();
        $I->setHeaders();
        $I->amBearerAuthenticated($token);
        $I->sendPOST($url, $postBody);
        return $I->grabResponse();
    }

    /**
     * Function get call for userID
     * @param ApiTester $I
     * @param $url
     * @param $token
     * @param $userID
     * @return string
     */
    public function getUserByID(ApiTester $I, $url, $token, $userID)
    {
        $I->clearHeaders();
        $I->setHeaders();
        $I->amBearerAuthenticated($token);
        $I->sendGET(str_replace("|", "%7C", $url));
        return $I->grabResponse();
    }

    /**
     * Function for delete call
     * @param ApiTester $I
     * @param $url
     * @param $token
     * @param $userID
     * @return string
     */
    public function deleteUserByID(ApiTester $I, $url, $token, $userID)
    {
        $I->clearHeaders();
        $I->setHeaders();
        $I->amBearerAuthenticated($token);
        $I->sendDELETE(str_replace("|", "%7C", $url));
        return $I->grabResponse();
    }
}
