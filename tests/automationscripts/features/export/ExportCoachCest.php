<?php

// Test Covers Scenarios related to export Coach

class ExportCoachCest
{

    //define End points used in the tests globally
    public $getLoginUrl = '/login';
    public $exportCoachUrl = "/registrants/export?type=";

    /**
     * @var helper\auth\loginHelper
     */
    protected $loginhelper;

    /**
     * @var validators\export\ExportValidator
     */
    protected $validator;

    /**
     * @var helper\export\ExportHelper
     */
    protected $helper;

    /**
     * @var utils\CommonUtils
     */
    protected $common;

    protected function _inject(validators\export\ExportValidator $validator, helper\export\ExportHelper $helper, helper\auth\loginHelper $loginhelper, utils\CommonUtils $common)
    {
        $this->helper = $helper;
        $this->validator = $validator;
        $this->loginhelper = $loginhelper;
        $this->common = $common;
    }

    /**
     * Test export coach scenarios
     * @group release
     * @group sanity
     * @group regression
     * @dataprovider exportcoach
     */
    public function validateExportCoach(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);
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
        $token = $I->grabDataFromResponseByJsonPath('access_token');
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "unauthorized") {
            $tokenParam = "ABCDEFGHIJ";
        }
        $this->common->waitTimeCall();
        $response = $this->helper->exportCall($I, $this->exportCoachUrl . 'coach',$tokenParam);
        $I->seeResponseCodeIs($dataBuilder['code']);
        if ($dataBuilder['key'] == "unauthorized") {
            $this->validator->validateExportResponse($response, $dataBuilder['expResponse'], $I, $this->common);
        }
    }

    /**
     * Test export coach error scenarios
     * @group regression
     * @dataprovider exportbadrequest
     */
    public function validateExportBadRequest(ApiTester $I, \Codeception\Example $dataBuilder)
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
        $token = $I->grabDataFromResponseByJsonPath('access_token');
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "unauthorized") {
            $tokenParam = "ABCDEFGHIJ";
        }
        $this->common->waitTimeCall();
        $response = $this->helper->exportCall($I, $this->exportCoachUrl . $dataBuilder['type'], $tokenParam);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $this->validator->validateExportResponse($response, $dataBuilder['expResponse'], $I, $this->common);
    }

// Data Providers for the Tests to be provided within Cest Classes

    /**
     * @return array
     */
    protected function exportcoach()
    {
        return [
            ['TestCase' => 'validateExportCoach', 'code' => "200", "expResponse" => "", 'key' => ''],
            ['TestCase' => 'validateExportCoachNullColumns', 'code' => "200", "expResponse" => "", 'key' => ''],
            ['TestCase' => 'validateExportCoachInvalidAuth', 'code' => "401", "expResponse" => "{\"errors\":[{\"error\":\"Invalid token.\"}]}", 'key' => 'unauthorized'],
            ['TestCase' => 'validateExportCoachNoAccess', 'code' => "403", "expResponse" => "{\"errors\":[{\"error\":\"Permission denied.\"}]}", 'key' => 'noaccess']

        ];
    }

    /**
     * @return array
     */
    protected function exportbadrequest()
    {
        return [
            ['TestCase' => 'validateExportTypeValueAsNull', 'code' => "400", "expResponse" => "{ \"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Type\", \"error\": \"The type field is required.\" } ] }", 'type' => null, 'key' => ''],
            ['TestCase' => 'validateExportTypeValueAsBlank', 'code' => "400", "expResponse" => "{ \"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Type\", \"error\": \"The type field is required.\" } ] }", 'type' => '', 'key' => ''],
            ['TestCase' => 'validateExportTypeValueAsInvalid', 'code' => "400", "expResponse" => "{ \"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Type\", \"error\": \"The selected type is invalid. Allowed types: PLAYER, COACH\" } ] }", 'type' => 'random', 'key' => '']

        ];
    }
}
