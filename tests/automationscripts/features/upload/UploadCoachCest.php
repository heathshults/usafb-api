<?php

// Test Covers Scenarios related to Upload Coach

class UploadCoachCest
{
    //define End points used in the tests globally

    public $getLoginUrl = '/login';
    public $uploadCoachUrl = "/registrants/import?type=";
    public $uploaddir = "/uploadcoach/";

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
     * @dataprovider uploadcoach
     */
    public function validateUploadCoach(ApiTester $I, \Codeception\Example $dataBuilder)
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

        //Upload Coach
        $filename = $this->uploaddir . $dataBuilder['FileName'];
        $response = $this->helper->uploadCall($I, $this->uploadCoachUrl . 'coach', "ABCDEFG", $filename, $dataBuilder['key']);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();

    }

    /**
     * @group regression
     * @dataprovider uploadbadrequest
     */
    public function validateBadRequest(ApiTester $I, \Codeception\Example $dataBuilder)
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

        //Upload Coach
        $filename = $this->uploaddir . $dataBuilder['FileName'];
        $response = $this->helper->uploadCall($I, $this->uploadCoachUrl . $dataBuilder['type'], "ABCDEFG", $filename);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        $this->validator->validateResponse($response, $dataBuilder['expResponse'], $I, $this->common);
    }

    /**
     * @return array
     */
    protected function uploadcoach()
    {
        return [
            ['TestCase' => 'validateUploadCoach', 'code' => "200", "expResponse" => "", "FileName" => "UploadCoach_Scenario1.csv", 'key' => '']
        ];
    }

    /**
     * @return array
     */
    protected function uploadbadrequest()
    {
        return [
            ['TestCase' => 'validateTypeValueAsNull', 'code' => "400", "expResponse" => "{ \"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Type\", \"error\": \"The type field is required.\" } ] }", 'type' => null, "FileName" => "UploadCoach_Scenario1.csv"],
            ['TestCase' => 'validateTypeValueAsBlank', 'code' => "400", "expResponse" => "{ \"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Type\", \"error\": \"The type field is required.\" } ] }", 'type' => '', "FileName" => "UploadCoach_Scenario1.csv"],
            ['TestCase' => 'validateTypeValueAsInvalid', 'code' => "400", "expResponse" => "{ \"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Type\", \"error\": \"The selected type is invalid. Allowed types: PLAYER, COACH\" } ] }", 'type' => 'random', "FileName" => "UploadCoach_Scenario1.csv"],
        ];
    }

}