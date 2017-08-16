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
     * Test upload coach scenarios
     * @group release
     * @group sanity
     * @group regression
     * @dataprovider uploadcoach
     */
    public function validateUploadCoach(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);
        $loginResponse = $this->loginhelper->postCall($I, $this->getLoginUrl, $this->common->loginPostRequest(null, $this->common->getEnvEmail("", $I), $this->common->getEnvPassword("", $I)));
        $token = $I->grabDataFromResponseByJsonPath('access_token');
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "unauthorized") {
            $tokenParam = "ABCDEFGHIJ";
        }
        $this->common->waitTimeCall();
        $filename = $this->uploaddir . $dataBuilder['FileName'];
        $response = $this->helper->uploadCall($I, $this->uploadCoachUrl . 'coach', $tokenParam, $filename, $dataBuilder['key']);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        $this->validator->validateUploadResponse($response, $dataBuilder['expResponse'], $I, $this->common);
    }

    /**
     * Test upload coach error scenarios
     * @group regression
     * @dataprovider uploadbadrequest
     */
    public function validateUploadBadRequest(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);
        $loginResponse = $this->loginhelper->postCall($I, $this->getLoginUrl, $this->common->loginPostRequest(null, $this->common->getEnvEmail("", $I), $this->common->getEnvPassword("", $I)));
        $token = $I->grabDataFromResponseByJsonPath('access_token');
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "unauthorized") {
            $tokenParam = "ABCDEFGHIJ";
        }
        $this->common->waitTimeCall();
        $filename = $this->uploaddir . $dataBuilder['FileName'];
        $response = $this->helper->uploadCall($I, $this->uploadCoachUrl . $dataBuilder['type'], $tokenParam, $filename);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        $this->validator->validateUploadResponse($response, $dataBuilder['expResponse'], $I, $this->common);
    }

// Data Providers for the Tests to be provided within Cest Classes

    /**
     * @return array
     */
    protected function uploadcoach()
    {
        return [
            ['TestCase' => 'validateUploadCoach', 'code' => "200", "expResponse" => "{\"processed\":4,\"errors\":0}", "FileName" => "UploadCoach_Scenario1.csv", 'key' => ''],
            ['TestCase' => 'validateUploadCoachNullColumns', 'code' => "200", "expResponse" => "{\"processed\":0,\"errors\":4}", "FileName" => "UploadCoach_Scenario2.csv", 'key' => ''],
            ['TestCase' => 'validateUploadCoachInvalidAuth', 'code' => "401", "expResponse" => "{\"errors\":[{\"error\":\"Invalid token.\"}]}", "FileName" => "UploadCoach_Scenario2.csv",'key' => 'unauthorized']
        ];
    }

    /**
     * @return array
     */
    protected function uploadbadrequest()
    {
        return [
            ['TestCase' => 'validateUploadTypeValueAsNull', 'code' => "400", "expResponse" => "{ \"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Type\", \"error\": \"The type field is required.\" } ] }", 'type' => null, "FileName" => "UploadCoach_Scenario1.csv", 'key' => ''],
            ['TestCase' => 'validateUploadTypeValueAsBlank', 'code' => "400", "expResponse" => "{ \"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Type\", \"error\": \"The type field is required.\" } ] }", 'type' => '', "FileName" => "UploadCoach_Scenario1.csv", 'key' => ''],
            ['TestCase' => 'validateUploadTypeValueAsInvalid', 'code' => "400", "expResponse" => "{ \"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Type\", \"error\": \"The selected type is invalid. Allowed types: PLAYER, COACH\" } ] }", 'type' => 'random', "FileName" => "UploadCoach_Scenario1.csv", 'key' => ''],
        ];
    }
}
