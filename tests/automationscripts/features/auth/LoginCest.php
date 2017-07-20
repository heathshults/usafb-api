<?php

// Test covers the end points
// "/login"
//Covers 200,400,401 codes

class LoginCest
{

    //Define End point URL's
    public $getLoginUrl = '/login';

    /**
     * @var validators\auth\LoginValidator
     */
    protected $validator;

    /**
     * @var helper\auth\LoginHelper
     */
    protected $helper;

    /**
     * @var utils\CommonUtils
     */
    protected $common;

    protected function _inject(validators\auth\LoginValidator $validator, helper\auth\LoginHelper $helper, utils\CommonUtils $common)
    {
        $this->helper = $helper;
        $this->validator = $validator;
        $this->common = $common;
    }

    /**
     * @group prereq
     */
    public function setEnvParam(ApiTester $I)
    {
        $this->common->setAuth0Credentials();
    }

    /**
     * @group release
     * @group sanity
     * @group regression
     * @dataprovider loginValidScenarios
     */
    //Incase to Skip Tests  * @skip

    public function verifyValidLogin(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);
        $response = $this->helper->postLogin($I, $this->getLoginUrl, $dataBuilder['postBody']);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        if ($dataBuilder['key'] == 'errors') {   //Sanity for Invalid Password with Valid Login
            $this->validator->validateErrResponse($response, $dataBuilder['expResponse'], $I, $this->common);
        } else {
            $this->validator->validateSuccResponse($response, $dataBuilder['expResponse'], $I, $this->common);
        }
    }

    /**
     * @group regression
     * @dataprovider loginInvalidScenarios
     */
    public function verifyInvalidLogin(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);
        $response = $this->helper->postLogin($I, $this->getLoginUrl, $dataBuilder['postBody']);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        $this->validator->validateErrResponse($response, $dataBuilder['expResponse'], $I, $this->common);
    }

//     Data Providers for the Tests to be provided within Cest Classes
    /**
     * @return array
     */
    protected function loginValidScenarios()
    {
        return [
            ['TestCase' => 'verifyValidLogin', 'code' => "200", "postBody" => ['email' => 'autouser@gmail.com', 'password' => 'password123'], "expResponse" => "{ \"expires_in\": 86400, \"scope\": \"openid profile email address phone\", \"token_type\": \"Bearer\" }", "key" => ''],   // Valid UserName/Password
            ['TestCase' => 'verifyValidLoginInvalidPassword', 'code' => "401", "postBody" => ['email' => 'autouser@gmail.com', 'password' => 'test123'], "expResponse" => "{ \"errors\":[ { \"error\": \"Wrong email or password.\" } ] }", "key" => 'errors'] // Valid UserName ,Invalid Password
        ];
    }

    /**
     * @return array
     */
    protected function loginInvalidScenarios()
    {
        return [
            ['TestCase' => 'verifyLoginWithBlankEmail', 'code' => "400", "postBody" => ['email' => '', 'password' => 'test'], "expResponse" => "{\"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Email\", \"error\": \"The email field is required.\" } ] }", "key" => 'errors'],  // Email Blank
            ['TestCase' => 'verifyLoginWithBlankPassword', 'code' => "400", "postBody" => ['email' => 'autouser@gmail.com', 'password' => ''], "expResponse" => "{\"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Password\", \"error\": \"The password field is required.\" } ] }", "key" => 'errors'], // Password Blank
            ['TestCase' => 'verifyLoginWithBlankEmail&Password', 'code' => "400", "postBody" => ['email' => '', 'password' => ''], "expResponse" => "{\"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Email\", \"error\": \"The email field is required.\" } ,{ \"code\": \"invalid_attribute\", \"title\": \"Invalid Password\", \"error\": \"The password field is required.\" }] }", "key" => 'errors'], // Email/Password Blank
            ['TestCase' => 'verifyLoginWithInvalidEmail&Password', 'code' => "400", "postBody" => ['email' => 'test@', 'password' => ''], "expResponse" => "{\"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Email\", \"error\": \"The email must be a valid email address.\" },{ \"code\": \"invalid_attribute\", \"title\": \"Invalid Password\", \"error\": \"The password field is required.\" } ] }", "key" => 'errors'], // Invalid Email/Blank Password
            ['TestCase' => 'verifyLoginWithInvalidEmail', 'code' => "400", "postBody" => ['email' => 'test@', 'password' => 'test'], "expResponse" => "{\"errors\":[ { \"code\": \"invalid_attribute\", \"title\": \"Invalid Email\", \"error\": \"The email must be a valid email address.\" } ] }", "key" => 'errors'] // Invalid Email
        ];
    }
}
