<?php


class UserCest
{

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
        $getLoginUrl = "/rest/auth/login";
        $loginResponse = $this->loginhelper->postLoginCall($I, $getLoginUrl, $dataBuilder['postBody']);
        $token = $I->grabDataFromResponseByJsonPath('access_token');
        $getUserUrl = "/rest/auth/user";
        $UserProfileResponse = $this->helper->getUserCall($I, $getUserUrl, $token[0], $this->common);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        $this->validator->verifyUserProfile($I, $UserProfileResponse, $dataBuilder['expResponse'], $this->common);
    }


    /**
     * @return array
     */
    protected function userdetails()
    {
        return [
            ['TestCase' => 'verifyUserProfile', 'code' => "200", "postBody" => ['email' => 'autouser@gmail.com', 'password' => 'password123'], "expResponse" => array('name' => 'autouser@gmail.com', 'nickname' => 'autouser', 'email' => 'autouser@gmail.com', 'email_verified' => false)]
        ];
    }

}
