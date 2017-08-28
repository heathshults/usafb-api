<?php

// Test Covers Scenarios related to export Player

class ExportPlayerCest
{
    //define End points used in the tests globally

    public $getLoginUrl = '/login';
    public $exportPlayerUrl = "/registrants/export?type=";

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
     * Test export player scenarios
     * @group release
     * @group sanity
     * @group regression
     * @dataprovider exportplayer
     */
    public function validateExportPlayer(ApiTester $I, \Codeception\Example $dataBuilder)
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
        $response = $this->helper->exportCall($I, $this->exportPlayerUrl . 'player', $tokenParam);
        $I->seeResponseCodeIs($dataBuilder['code']);
        if ($dataBuilder['key'] == "unauthorized") {
            $this->validator->validateExportResponse($response, $dataBuilder['expResponse'], $I, $this->common);
        }

    }

    /**
     * @return array
     */
    protected function exportplayer()
    {
        return [
            ['TestCase' => 'validateExportPlayer', 'code' => "200", "expResponse" => "", 'key' => ''],
            ['TestCase' => 'validateExportPlayerNullColumns', 'code' => "200", "expResponse" => "", 'key' => ''],
            ['TestCase' => 'validateExportPlayersInvalidAuth', 'code' => "401", "expResponse" => "{\"errors\":[{\"error\":\"Invalid token.\"}]}", 'key' => 'unauthorized'],
            ['TestCase' => 'validateExportPlayersNoAccess', 'code' => "401", "expResponse" => "{\"errors\":[{\"error\":\"Invalid token.\"}]}", 'key' => 'noaccess']
        ];
    }
}
