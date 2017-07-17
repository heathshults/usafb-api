<?php

// Test covers the end points
// "/auth/user"   - Get User Profile for logged in user

//Test Scenerios covered 401 , 200

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
     * @group release
     * @group sanity
     * @group regression
     * @dataprovider userdetails
     */
    //Incase to Skip Tests  * @skip

    public function verifyUserProfile(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);
        $loginResponse = $this->loginhelper->postLogin($I, $this->getLoginUrl, $dataBuilder['postBody']);
        $token = $I->grabDataFromResponseByJsonPath('access_token');
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "unauthorized") {
            $tokenParam = "ABCDEFGHIJ";
        }
        $userProfileResponse = $this->helper->getUserByToken($I, $this->getUserProfileUrl, $tokenParam,$dataBuilder['key']);
        codecept_debug($userProfileResponse);

        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();

        $this->validator->verifyUserProfile($I, $userProfileResponse, $dataBuilder['expResponse'], $this->common);
    }


    /**

     * @group regression
     * @dataprovider userdetailsErr
     */
    //Incase to Skip Tests  * @skip

    public function verifyUserProfileErr(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);
        $loginResponse = $this->loginhelper->postLogin($I, $this->getLoginUrl, $dataBuilder['postBody']);
        $token = $I->grabDataFromResponseByJsonPath('access_token');
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "EmptyToken") {
            $tokenParam = "";
        }
        $userProfileResponse = $this->helper->getUserByToken($I, $this->getUserProfileUrl, $tokenParam,$dataBuilder['key']);
        codecept_debug($userProfileResponse);

        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();

        $this->validator->verifyUserProfile($I, $userProfileResponse, $dataBuilder['expResponse'], $this->common);
    }



    /**
     * @return array
     */
    protected function userdetails()
    {
        return [
            ['TestCase' => 'verifyUserProfile', 'code' => "200", "postBody" => ['email' => 'autouser@gmail.com', 'password' => 'password123'], "expResponse" => "{ \"name\": \"autouser@gmail.com\", \"nickname\": \"autouser\", \"email\": \"autouser@gmail.com\", \"email_verified\": true, \"http://soccer.com/metadata\":{ \"firstName\": \"\", \"lastName\": \"\", \"city\": \"\", \"state\": \"\", \"postalCode\": \"\", \"competition\": \"\", \"verification_email_sent\": true, \"roles\":[ 1, 3 ] } }", 'key' => 'authorized'],
            ['TestCase' => 'verifyUserProfileWithInvalidToken', 'code' => "401", "postBody" => ['email' => 'autouser@gmail.com', 'password' => 'password123'], "expResponse" => "{\"errors\":[{\"error\":\"Invalid token.\"}]}", 'key' => 'unauthorized']
        ];
    }

    /**
     * @return array
     */
    protected function userdetailsErr()
    {
        return [
            ['TestCase' => 'verifyUserProfileWithNoAuthHeader', 'code' => "401", "postBody" => ['email' => 'autouser@gmail.com', 'password' => 'password123'], "expResponse" => "{\"errors\":[{\"error\":\"No authorization header provided.\"}]}", 'key' => 'NoHeader'],
            ['TestCase' => 'verifyUserProfileWithInvalidTokenType', 'code' => "401", "postBody" => ['email' => 'autouser@gmail.com', 'password' => 'password123'], "expResponse" => "{\"errors\":[{\"error\":\"Invalid token type.\"}]}", 'key' => 'NoBearer'],
            ['TestCase' => 'verifyUserProfileWithBlankToken', 'code' => "401", "postBody" => ['email' => 'autouser@gmail.com', 'password' => 'password123'], "expResponse" => "{\"errors\":[{\"error\":\"No token provided.\"}]}", 'key' => 'EmptyToken'],

        ];
    }



}
