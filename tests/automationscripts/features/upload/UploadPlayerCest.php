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
     * @group release
     * @group sanity
     * @group regression
     * @dataprovider uploadplayer
     */
    //Incase to Skip Tests  * @skip
    public function validateUploadPlayer(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);

//        // Login call
//        $loginResponse = $this->loginhelper->postCall($I, $this->getLoginUrl, $this->common->loginPostRequest(null, $this->common->getEnvEmail("", $I), $this->common->getEnvPassword("", $I)));
//        $token = $I->grabDataFromResponseByJsonPath('access_token');
//        $tokenParam = $token[0];
//        if ($dataBuilder['key'] == "unauthorized") {
//            $tokenParam = "ABCDEFGHIJ";
//        }

        //Upload Player
        $response = $this->helper->uploadCall($I, $this->uploadPlayerUrl . 'player', "ABCDEFG", $this->uploaddir . $dataBuilder['FileName']);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        $this->validator->validateResponse($response, $dataBuilder['expResponse'], $I, $this->common);

    }

    /**
     * @return array
     */
    protected function uploadplayer()
    {
        return [
            ['TestCase' => 'validateUploadPlayer', 'code' => "200", "expResponse" => "{\"processed\":8,\"errors\":0}", "FileName" => "UploadPlayer_Scenario1.csv", 'key' => '']
        ];
    }

}
