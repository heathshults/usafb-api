<?php

// Test covers the end points
// "/auth/user"   - Get User Profile for logged in user

//Test Scenarios covered 401 , 200

class UserCest
{
    //define End points used in the tests globally
    public $getLoginUrl = '/login';
    public $getUserProfileUrl = "/me";
    public $postCreateUserUrl = "/users";
    public $getUserByIDUrl = "/users/";

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


    /**
     * Tests to validate create user
     * @group release
     * @group sanity
     * @group regression
     * @dataprovider createUser
     */
    public function verifyCreateUser(ApiTester $I, \Codeception\Example $dataBuilder)
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
        $seq = $this->common->randomNumber();
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "unauthorized") {
            $tokenParam = "ABCDEFGHIJ";
        }

        // Create User
        $this->common->waitTimeCall();
        $createUserResponse = $this->helper->postCall($I, $this->postCreateUserUrl, str_replace("-seq", $seq, $dataBuilder['postBodyUser']), $tokenParam);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();

        if ($dataBuilder['key'] == 'validations' || $dataBuilder['key'] == 'unauthorized' || $dataBuilder['key'] == 'noaccess') {

            $this->validator->validateResponse($createUserResponse, $dataBuilder['expResponse'], $I, $this->common);

        } else if ($dataBuilder['key'] == 'create') {

            //Get User ID
            $this->common->waitTimeCall();
            $userId = $I->grabDataFromResponseByJsonPath('user_id');
            $getUserResponse = $this->helper->getUserByID($I, $this->getUserByIDUrl . $userId[0], $token[0], $userId[0]);
            $I->seeResponseCodeIs($dataBuilder['code']);

            //Validate Create User Data
            $finalExpectedResponse = str_replace('userID', $userId[0], str_replace("-seq", $seq, $dataBuilder['expResponse']));
            $this->validator->validateResponse($getUserResponse, $finalExpectedResponse, $I, $this->common);
            $userMetaData = str_replace('userID', $userId[0], str_replace("-seq", $seq, $dataBuilder['metadataResponse']));
            $userMetaDataResponse = $I->grabDataFromResponseByJsonPath('user_metadata');
            $this->validator->validateResponse(json_encode($userMetaDataResponse[0]), $userMetaData, $I, $this->common);
            $this->validator->validateKeyNotEquals(json_encode($userMetaDataResponse[0]), "created_by", "", $I, $this->common);

            // After Validation of Created User Delete User
            $this->common->waitTimeCall();
            $this->helper->deleteUserByID($I, $this->getUserByIDUrl . $userId[0], $token[0], $userId[0]);
            $I->seeResponseCodeIs($dataBuilder['code']);

            // Get User after deletion should return 404
            $this->common->waitTimeCall();
            $getUserResponse = $this->helper->getUserByID($I, $this->getUserByIDUrl . $userId[0], $token[0], $userId[0]);
            $I->seeResponseCodeIs($dataBuilder['errorResponseCode']);
            $I->seeResponseIsJson();
            $this->validator->validateResponse($getUserResponse, $dataBuilder['errorResponse'], $I, $this->common);

            // Perform Delete for no user existing in System should return 404
            $this->common->waitTimeCall();
            $getDeleteResponse = $this->helper->deleteUserByID($I, $this->getUserByIDUrl . $userId[0], $token[0], $userId[0]);
            $I->seeResponseCodeIs($dataBuilder['errorResponseCode']);
            $this->validator->validateResponse($getDeleteResponse, $dataBuilder['errorResponse'], $I, $this->common);
        }
    }

    /**
     * Tests to valdiate create user validations
     * @group regression
     * @dataprovider createUserValidations
     */
    public function verifyCreateUserValidations(ApiTester $I, \Codeception\Example $dataBuilder)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);

        // Login Call
        $loginResponse = $this->loginhelper->postCall($I, $this->getLoginUrl, $this->common->loginPostRequest(null, $this->common->getEnvEmail("", $I), $this->common->getEnvPassword("", $I)));
        $token = $I->grabDataFromResponseByJsonPath('access_token');

        // Create User
        $createUserResponse = $this->helper->postCall($I, $this->postCreateUserUrl, $dataBuilder['postBodyUser'], $token[0]);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();

        // Validate
        $this->validator->validateResponse($createUserResponse, $dataBuilder['expResponse'], $I, $this->common);
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

    /**
     * @return array
     */
    protected function createUser()
    {
        return [
            ['TestCase' => 'verifyCreateSuperUser', 'code' => "200", "errorResponseCode" => "404", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => '1', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890'], "expResponse" => "{ \"email\": \"autouser-seq@gmail.com\", \"email_verified\": false, \"user_id\": \"userID\"}", "errorResponse" => "{ \"errors\":[ { \"error\": \"The user does not exist.\" } ] }", 'metadataResponse' => "{ \"first_name\": \"AutoFirst-seq\", \"last_name\": \"AutoLast-seq\", \"city\": \"Frisco\", \"phone_number\": \"1234567890\", \"state\": \"TX\", \"postal_code\": \"75034\", \"roles\":[ \"Super User\" ] }", 'key' => 'create'],
            ['TestCase' => 'verifyCreateStakeholderUser', 'code' => "200", "errorResponseCode" => "404", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => '3', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890'], "expResponse" => "{ \"email\": \"autouser-seq@gmail.com\", \"email_verified\": false, \"user_id\": \"userID\"}", "errorResponse" => "{ \"errors\":[ { \"error\": \"The user does not exist.\" } ] }", 'metadataResponse' => "{ \"first_name\": \"AutoFirst-seq\", \"last_name\": \"AutoLast-seq\", \"city\": \"Frisco\", \"phone_number\": \"1234567890\", \"state\": \"TX\", \"postal_code\": \"75034\", \"roles\":[ \"Stakeholder User\" ] }", 'key' => 'create'],
            ['TestCase' => 'verifyCreatePartnerUser', 'code' => "200", "errorResponseCode" => "404", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => '4', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890'], "expResponse" => "{ \"email\": \"autouser-seq@gmail.com\", \"email_verified\": false, \"user_id\": \"userID\"}", "errorResponse" => "{ \"errors\":[ { \"error\": \"The user does not exist.\" } ] }", 'metadataResponse' => "{ \"first_name\": \"AutoFirst-seq\", \"last_name\": \"AutoLast-seq\", \"city\": \"Frisco\", \"phone_number\": \"1234567890\", \"state\": \"TX\", \"postal_code\": \"75034\", \"roles\":[ \"Partner User\" ] }", 'key' => 'create'],
            ['TestCase' => 'verifyCreateLeague/Club/TeamUser', 'code' => "200", "errorResponseCode" => "404", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => '5', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890'], "expResponse" => "{ \"email\": \"autouser-seq@gmail.com\", \"email_verified\": false, \"user_id\": \"userID\"}", "errorResponse" => "{ \"errors\":[ { \"error\": \"The user does not exist.\" } ] }", 'metadataResponse' => "{ \"first_name\": \"AutoFirst-seq\", \"last_name\": \"AutoLast-seq\", \"city\": \"Frisco\", \"phone_number\": \"1234567890\", \"state\": \"TX\", \"postal_code\": \"75034\", \"roles\":[ \"League/Club/Team User\" ] }", 'key' => 'create'],
            ['TestCase' => 'verifyCreateAdminUser', 'code' => "200", "errorResponseCode" => "404", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => '2', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890'], "expResponse" => "{ \"email\": \"autouser-seq@gmail.com\", \"email_verified\": false, \"user_id\": \"userID\"}", "errorResponse" => "{ \"errors\":[ { \"error\": \"The user does not exist.\" } ] }", 'metadataResponse' => "{ \"first_name\": \"AutoFirst-seq\", \"last_name\": \"AutoLast-seq\", \"city\": \"Frisco\", \"phone_number\": \"1234567890\", \"state\": \"TX\", \"postal_code\": \"75034\", \"roles\":[ \"Admin User\" ] }", 'key' => 'create'],
            ['TestCase' => 'verifyCreateUserWithEmailExists', 'code' => "409", "postBodyUser" => ['first_name' => 'AutoFirst', 'last_name' => 'AutoLast', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '', 'role' => '1', 'email' => 'autouser@gmail.com', 'phone_number' => '1234567890'], "expResponse" => "{ \"errors\":[ { \"error\": \"The email address submitted already exists in the system.\" } ] }", 'key' => 'validations'],
            ['TestCase' => 'verifyCreateUserInvalidToken', 'code' => "401", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => '1', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890'], "expResponse" => "{\"errors\":[{\"error\":\"Invalid token.\"}]}", 'key' => 'unauthorized'],
            ['TestCase' => 'verifyCreateUserNoPermission', 'code' => "403", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => '1', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890'], "expResponse" => "{\"errors\":[{\"error\":\"Permission denied.\"}]}", 'key' => 'noaccess'],
            ['TestCase' => 'verifyCreateUserNoPermissionAdminUser', 'code' => "403", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => '1', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890'], "expResponse" => "{\"errors\":[{\"error\":\"Permission denied.\"}]}", 'key' => 'adminrole']
        ];
    }

    /**
     * @return array
     */
    protected function createUserValidations()
    {
        return [
            ['TestCase' => 'verifyCreateUserValidations', 'code' => "400", "postBodyUser" => ['first_name' => '', 'last_name' => '', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '', 'role' => '', 'email' => '', 'phone_number' => 'A1234567890'], "expResponse" => "{\"errors\":[{\"code\":\"invalid_attribute\",\"title\":\"Invalid First_name\",\"error\":\"The first name field is required.\"},{\"code\":\"invalid_attribute\",\"title\":\"Invalid Last_name\",\"error\":\"The last name field is required.\"},{\"code\":\"invalid_attribute\",\"title\":\"Invalid Email\",\"error\":\"The email field is required.\"},{\"code\":\"invalid_attribute\",\"title\":\"Invalid Phone_number\",\"error\":\"The phone number must be a number.\"},{\"code\":\"invalid_attribute\",\"title\":\"Invalid Role\",\"error\":\"The role field is required.\"}]}", 'key' => 'validations'],
            ['TestCase' => 'verifyCreateUserInvalidEmail', 'code' => "400", "postBodyUser" => ['first_name' => '', 'last_name' => '', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '', 'role' => '', 'email' => 'autouser', 'phone_number' => '1234567890'], "expResponse" => "{\"errors\":[{\"code\":\"invalid_attribute\",\"title\":\"Invalid First_name\",\"error\":\"The first name field is required.\"},{\"code\":\"invalid_attribute\",\"title\":\"Invalid Last_name\",\"error\":\"The last name field is required.\"},{\"code\":\"invalid_attribute\",\"title\":\"Invalid Email\",\"error\":\"The email must be a valid email address.\"},{\"code\":\"invalid_attribute\",\"title\":\"Invalid Role\",\"error\":\"The role field is required.\"}]}", 'key' => 'validations']
        ];
    }
}
