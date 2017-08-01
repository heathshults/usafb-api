<?php

// Test Covers Scenarios related to Upload Player

class UploadPlayerCest
{
    //define End points used in the tests globally

    public $getLoginUrl = '/login';
    public $uploadPlayerUrl = "/registrants/import";


    /**
     * @var helper\auth\loginHelper
     */
    protected $loginhelper;

    /**
     * @var validators\uploadplayer\UploadPlayerValidator
     */
    protected $validator;

    /**
     * @var helper\uploadplayer\UploadPlayerHelper
     */
    protected $helper;

    /**
     * @var utils\CommonUtils
     */
    protected $common;


    protected function _inject(validators\uploadplayer\UploadPlayerValidator $validator, helper\uploadplayer\UploadPlayerHelper $helper, helper\auth\loginHelper $loginhelper, utils\CommonUtils $common)
    {
        $this->helper = $helper;
        $this->validator = $validator;
        $this->loginhelper = $loginhelper;
        $this->common = $common;
    }

    /**
     * @group release
     * @group sanity
     * @group regression_noexecute
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
        $this->helper->uploadPlayerCall($I, $this->uploadPlayerUrl, "ABCDEFG", $dataBuilder['FileName']);
    }

    /**
     * @return array
     */
    protected function uploadplayer()
    {
        return [
              ['TestCase' => 'validateUploadPlayer', 'code' => "200", "expResponse" => "", "FileName" => "UploadPlayer_Scenario1.csv", 'key' => ''],
//            ['TestCase' => 'validateUploadPlayerNotNullColumns', 'code' => "200", "expResponse" => "", 'key' => ''],
//            ['TestCase' => 'validateUploadPlayerNullColumns', 'code' => "200", "expResponse" => "", 'key' => ''],
//            ['TestCase' => 'verifyUserProfileWithInvalidToken', 'code' => "401", "expResponse" => "{\"errors\":[{\"error\":\"Invalid token.\"}]}", 'key' => 'unauthorized']
        ];
    }

}
