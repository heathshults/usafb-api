<?php

// Test covers the end points
// "/auth/user"   - Get User Profile for logged in user

//Test Scenarios covered 401 , 200

class UserCest
{
    //define End points used in the tests globally
    public $getLoginUrl = '/login';
    public $getUserProfileUrl = "/auth/user";

    /**
     * @var helper\auth\loginHelper
     */
    protected $loginhelper;

    /**
     * @var validators\auth\UserValidator
     */
    protected $validator;

    /**
     * @var helper\auth\UserHelper
     */
    protected $helper;

    /**
     * @var utils\CommonUtils
     */
    protected $common;

    protected function _inject(validators\auth\UserValidator $validator, helper\auth\UserHelper $helper, helper\auth\loginHelper $loginhelper, utils\CommonUtils $common)
    {
        $this->helper = $helper;
        $this->validator = $validator;
        $this->loginhelper = $loginhelper;
        $this->common = $common;
    }

    /**
     * Test to verify user profile scenarios
     * @group release
     * @group sanity
     * @group regression
     * @dataprovider userdetails
     */
    public function verifyUserProfile(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);
        $loginResponse = $this->loginhelper->postCall($I, $this->getLoginUrl, $this->common->loginPostRequest(null, $this->common->getEnvEmail("", $I), $this->common->getEnvPassword("", $I)));
        $token = $I->grabDataFromResponseByJsonPath('access_token');
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "unauthorized") {
            $tokenParam = "ABCDEFGHIJ";
        }
        $userProfileResponse = $this->helper->getUserByToken($I, $this->getUserProfileUrl, $tokenParam, $dataBuilder['key']);

        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        $this->validator->verifyUserProfile($I, $userProfileResponse, $dataBuilder['expResponse'], $this->common);
    }

    /**
     * Tests to validate user profile error scenarios
     * @group regression
     * @dataprovider userdetailsErr
     */
    public function verifyUserProfileErr(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);
        $loginResponse = $this->loginhelper->postCall($I, $this->getLoginUrl, $this->common->loginPostRequest(null, $this->common->getEnvEmail("", $I), $this->common->getEnvPassword("", $I)));
        $token = $I->grabDataFromResponseByJsonPath('access_token');
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "EmptyToken") {
            $tokenParam = "";
        }
        $userProfileResponse = $this->helper->getUserByToken($I, $this->getUserProfileUrl, $tokenParam, $dataBuilder['key']);

        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        $this->validator->verifyUserProfile($I, $userProfileResponse, $dataBuilder['expResponse'], $this->common);
    }

// Data Providers for the Tests to be provided within Cest Classes

    /**
     * @return array
     */
    protected function userdetails()
    {
        return [
            ['TestCase' => 'verifyUserProfile', 'code' => "200", "expResponse" => "{ \"name\": \"autouser@gmail.com\", \"nickname\": \"autouser\", \"email\": \"autouser@gmail.com\", \"email_verified\": true}", 'key' => 'authorized'],
            ['TestCase' => 'verifyUserProfileWithInvalidToken', 'code' => "401", "expResponse" => "{\"errors\":[{\"error\":\"Invalid token.\"}]}", 'key' => 'unauthorized']
        ];
    }

    /**
     * @return array
     */
    protected function userdetailsErr()
    {
        return [
            ['TestCase' => 'verifyUserProfileWithNoAuthHeader', 'code' => "401", "expResponse" => "{\"errors\":[{\"error\":\"No authorization header provided.\"}]}", 'key' => 'NoHeader'],
            ['TestCase' => 'verifyUserProfileWithInvalidTokenType', 'code' => "401", "expResponse" => "{\"errors\":[{\"error\":\"Invalid token type.\"}]}", 'key' => 'NoBearer'],
            ['TestCase' => 'verifyUserProfileWithBlankToken', 'code' => "401", "expResponse" => "{\"errors\":[{\"error\":\"No token provided.\"}]}", 'key' => 'EmptyToken'],

        ];
    }
}
