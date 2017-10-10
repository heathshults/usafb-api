<?php

// Test Covers Scenarios related to Upload Player

class UploadPlayerCest
{
    //define End points used in the tests globally

    public $getLoginUrl = '/login';
    public $uploadPlayerUrl = "/registrants/import?type=";
    public $uploaddir = "/uploadplayer/";

    /**
     * @var helper\auth\loginHelper
     */
    protected $loginhelper;

    /**
     * @var validators\upload\UploadValidator
     */
    protected $validator;

    /**
     * @var helper\upload\UploadHelper
     */
    protected $helper;

    /**
     * @var utils\CommonUtils
     */
    protected $common;


    protected function _inject(validators\upload\UploadValidator $validator, helper\upload\UploadHelper $helper, helper\auth\loginHelper $loginhelper, utils\CommonUtils $common)
    {
        $this->helper = $helper;
        $this->validator = $validator;
        $this->loginhelper = $loginhelper;
        $this->common = $common;
    }

    /**
     * Test upload player scenarios
     * @group release
     * @group sanity
     * @group regression
     * @dataprovider uploadplayer
     */
    public function validateUploadPlayer(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);

        // Login Call
        if ($dataBuilder['key'] == "noaccess") {
            $postbody = $this->common->loginPostRequest(null, $this->common->getEnvEmail("norole", $I), $this->common->getEnvPassword("norole", $I));
        } else  if ($dataBuilder['key'] == "adminrole")  {
            $postbody = $this->common->loginPostRequest(null, $this->common->getEnvEmail("adminrole", $I), $this->common->getEnvPassword("adminrole", $I));
        }
        else
        {
            $postbody = $this->common->loginPostRequest(null, $this->common->getEnvEmail("", $I), $this->common->getEnvPassword("", $I));
        }


        $loginResponse = $this->loginhelper->postCall($I, $this->getLoginUrl, $postbody);
        $token = $I->grabDataFromResponseByJsonPath('id_token');
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "unauthorized") {
            $tokenParam = "ABCDEFGHIJ";
        }
        $this->common->waitTimeCall();
        $response = $this->helper->uploadCall($I, $this->uploadPlayerUrl . 'player', $tokenParam, $this->uploaddir . $dataBuilder['FileName']);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        $this->validator->validateUploadResponse($response, $dataBuilder['expResponse'], $I, $this->common);
    }

    /**
     * @return array
     */
    protected function uploadplayer()
    {
        return [
            ['TestCase' => 'validateUploadPlayer', 'code' => "200", "expResponse" => "{\"processed\":4,\"errors\":0}", "FileName" => "UploadPlayer_Scenario1.csv", 'key' => ''],
            ['TestCase' => 'validateUploadPlayerNullColumns', 'code' => "200", "expResponse" => "{\"processed\":0,\"errors\":4}", "FileName" => "UploadPlayer_Scenario2.csv", 'key' => ''],
            ['TestCase' => 'validateUploadPlayersInvalidAuth', 'code' => "401", "expResponse" => "{\"errors\":[{\"error\":\"Invalid token.\"}]}", "FileName" => "UploadPlayer_Scenario2.csv", 'key' => 'unauthorized'],
            ['TestCase' => 'validateUploadPlayersNoAccess', 'code' => "403", "expResponse" => "{\"errors\":[{\"error\":\"Permission denied.\"}]}", "FileName" => "UploadPlayer_Scenario2.csv", 'key' => 'noaccess'],


        ];
    }
}
