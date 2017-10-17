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
    public $getUsersUrl = "/users?page=";
    public $getUserLogs = "/users/";

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
        $token = $I->grabDataFromResponseByJsonPath('id_token');
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
        $token = $I->grabDataFromResponseByJsonPath('id_token');
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
        $token = $I->grabDataFromResponseByJsonPath('id_token');
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
            $userId = $I->grabDataFromResponseByJsonPath('data.id');
            $getUserResponse = $this->helper->getUserByID($I, $this->getUserByIDUrl . $userId[0], $token[0], $userId[0]);
            $I->seeResponseCodeIs($dataBuilder['code']);

            //Validate Create User Data

            $data = str_replace('userID', $userId[0], str_replace("-seq", $seq, $dataBuilder['expResponse']));
            $dataResponse = $I->grabDataFromResponseByJsonPath('data');
            $this->validator->validateResponse(json_encode($dataResponse[0]), $data, $I, $this->common);
            $this->validator->validateKeyNotEquals(json_encode($dataResponse[0]), "created_by", "", $I, $this->common);

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


            // Validate the userlogs created once user is created
            $getUserLogsResponse = $this->helper->getUsers($I, $this->getUserLogs .$userId[0] . '/logs?page=1&per_page=10', $token[0]);
            $I->seeResponseCodeIs($dataBuilder['code']);
            $I->seeResponseIsJson();
            $Data = $I->grabDataFromResponseByJsonPath('data');
            $Data=$this->common->unsetKeyJson($Data[0],"created_at");
            $this->validator->validateResponse(json_encode($Data),str_replace("autouserid",$userId[0],str_replace("seq", $seq, $dataBuilder['expUserLogsResponse'])) , $I, $this->common);
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
        $token = $I->grabDataFromResponseByJsonPath('id_token');

        // Create User
        $createUserResponse = $this->helper->postCall($I, $this->postCreateUserUrl, $dataBuilder['postBodyUser'], $token[0]);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();

        // Validate
        $this->validator->validateResponse($createUserResponse, $dataBuilder['expResponse'], $I, $this->common);
    }


    /**
     * Tests to verify get users
     * @group release
     * @group sanity
     * @group regression
     * @group auth
     * @dataprovider getUsers
     */
    public function verifyGetUsers(ApiTester $I, \Codeception\Example $dataBuilder, \Codeception\Scenario $scenario)
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
        $token = $I->grabDataFromResponseByJsonPath('id_token');
        $tokenParam = $token[0];
        if ($dataBuilder['key'] == "invalid") {
            $tokenParam = "ABCDEFGHIJ";
        }
        // Get Users
        $this->common->waitTimeCall();
        $getUsersResponse = $this->helper->getUsers($I, $this->getUsersUrl . $dataBuilder['pageNo'] . '&per_page='. $dataBuilder['pageSize'] . '&sort=' . $dataBuilder['Sortable'] . '&q=' . $dataBuilder['SearchString'], $tokenParam);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();
        if ($dataBuilder['expResponse'] != null) {
            if ($dataBuilder['expResponse'] == 'emptyList') {
                $I->assertEquals(count($I->grabDataFromResponseByJsonPath('data')[0]), 0);
            } else {
                $Data = $I->grabDataFromResponseByJsonPath('data');
                $this->validator->validateKeyEquals(json_encode($Data[0][0]), "email", $dataBuilder['expResponse'][0], $I, $this->common);
                $this->validator->validateKeyEquals(json_encode($Data[0][1]), "email", $dataBuilder['expResponse'][1], $I, $this->common);
                $this->validator->validateKeyEquals(json_encode($Data[0][2]), "email", $dataBuilder['expResponse'][2], $I, $this->common);
            }
        }

    }


    /**
     * Tests to verify get users other scenarios
     * @group regression
     * @group auth
     * @dataprovider getUsersOtherScenarios
     */
    public function verifyGetUsersOtherScenarios(ApiTester $I, \Codeception\Example $dataBuilder, \Codeception\Scenario $scenario)
    {
        $I->wantToTest($dataBuilder['TestCase']);
        $I->comment($dataBuilder['TestCase']);

        // Login Call
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
        $token = $I->grabDataFromResponseByJsonPath('id_token');
        $tokenParam = $token[0];

        if ($dataBuilder['key'] == "invalid") {
            $tokenParam = "ABCDEFGHIJ";
        }

        // Get Users
        $this->common->waitTimeCall();
        $getUsersResponse = $this->helper->getUsers($I, $this->getUsersUrl . $dataBuilder['pageNo'] . '&per_page='. $dataBuilder['pageSize'] . '&sort=' . $dataBuilder['Sortable'] . '&q=' . $dataBuilder['SearchString'], $tokenParam);
        $I->seeResponseCodeIs($dataBuilder['code']);
        $I->seeResponseIsJson();

        if ($dataBuilder['expResponse'] != null) {
            if ($dataBuilder['expResponse'] == 'emptyList') {

                $I->assertEquals(count($I->grabDataFromResponseByJsonPath('data')[0]), 0);
            } else {
                $Data = $I->grabDataFromResponseByJsonPath('data');
                $this->validator->validateKeyEquals(json_encode($Data[0][0]), "email", $dataBuilder['expResponse'][0], $I, $this->common);
                $this->validator->validateKeyEquals(json_encode($Data[0][1]), "email", $dataBuilder['expResponse'][1], $I, $this->common);
                $this->validator->validateKeyEquals(json_encode($Data[0][2]), "email", $dataBuilder['expResponse'][2], $I, $this->common);
            }
        }

    }


// Data Providers for the Tests to be provided within Cest Classes

    /**
     * @return array
     */
    protected function userdetails()
    {
        return [
            ['TestCase' => 'verifyUserProfile', 'code' => "200", "expResponse" => "{ \"name\": \"footballautomation@gmail.com\", \"nickname\": \"footballautomation\", \"email\": \"footballautomation@gmail.com\", \"email_verified\": true}", 'key' => 'authorized'],
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
            ['TestCase' => 'verifyCreateSuperUser', 'code' => "200", "errorResponseCode" => "404", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => 'Super User', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890','county'=>'US','organization'=>'Rising Stars'], "expResponse" => "{ \"id\": \"userID\", \"email\": \"autouser-seq@gmail.com\", \"first_name\": \"AutoFirst-seq\", \"last_name\": \"AutoLast-seq\", \"phone_number\": \"1234567890\", \"city\": \"Frisco\", \"state\": \"TX\", \"postal_code\": \"75034\", \"roles\": [ \"Super User\" ], \"email_verified\": false, \"status\": \"Enabled\" ,\"county\": \"US\",\"organization\": \"Rising Stars\"}", "errorResponse" => "{ \"errors\":[ { \"error\": \"The user does not exist.\" } ] }", "expUserLogsResponse"=>"{ \"data\": [ { \"old_value\": null, \"new_value\": null, \"user\": \"autouserid\", \"data_field\": null, \"created_by\": \"FOOTBALL USAFB\", \"action\": \"CREATE\" } ] }", 'key' => 'create'],
            ['TestCase' => 'verifyCreateStakeholderUser', 'code' => "200", "errorResponseCode" => "404", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => 'Stakeholder User', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890','county'=>'US','organization'=>'Rising Stars'], "expResponse" =>"{ \"id\": \"userID\", \"email\": \"autouser-seq@gmail.com\", \"first_name\": \"AutoFirst-seq\", \"last_name\": \"AutoLast-seq\", \"phone_number\": \"1234567890\", \"city\": \"Frisco\", \"state\": \"TX\", \"postal_code\": \"75034\", \"roles\": [ \"Stakeholder User\" ], \"email_verified\": false, \"status\": \"Enabled\" ,\"county\": \"US\",\"organization\": \"Rising Stars\"}" , "errorResponse" => "{ \"errors\":[ { \"error\": \"The user does not exist.\" } ] }","expUserLogsResponse"=>"{ \"data\": [ { \"old_value\": null, \"new_value\": null, \"user\": \"autouserid\", \"data_field\": null, \"created_by\": \"FOOTBALL USAFB\", \"action\": \"CREATE\" } ] }", 'key' => 'create'],
            ['TestCase' => 'verifyCreatePartnerUser', 'code' => "200", "errorResponseCode" => "404", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => 'Partner User', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890','county'=>'US','organization'=>'Rising Stars'], "expResponse" => "{ \"id\": \"userID\", \"email\": \"autouser-seq@gmail.com\", \"first_name\": \"AutoFirst-seq\", \"last_name\": \"AutoLast-seq\", \"phone_number\": \"1234567890\", \"city\": \"Frisco\", \"state\": \"TX\", \"postal_code\": \"75034\", \"roles\": [ \"Partner User\" ], \"email_verified\": false, \"status\": \"Enabled\",\"county\": \"US\",\"organization\": \"Rising Stars\"}", "errorResponse" => "{ \"errors\":[ { \"error\": \"The user does not exist.\" } ] }","expUserLogsResponse"=>"{ \"data\": [ { \"old_value\": null, \"new_value\": null, \"user\": \"autouserid\", \"data_field\": null, \"created_by\": \"FOOTBALL USAFB\", \"action\": \"CREATE\" } ] }", 'key' => 'create'],
            ['TestCase' => 'verifyCreateLeague/Club/TeamUser', 'code' => "200", "errorResponseCode" => "404", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => 'League/Club/Team User', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890','county'=>'US','organization'=>'Rising Stars'], "expResponse" => "{ \"id\": \"userID\", \"email\": \"autouser-seq@gmail.com\", \"first_name\": \"AutoFirst-seq\", \"last_name\": \"AutoLast-seq\", \"phone_number\": \"1234567890\", \"city\": \"Frisco\", \"state\": \"TX\", \"postal_code\": \"75034\", \"roles\": [ \"League/Club/Team User\" ], \"email_verified\": false, \"status\": \"Enabled\" ,\"county\": \"US\",\"organization\": \"Rising Stars\"}", "errorResponse" => "{ \"errors\":[ { \"error\": \"The user does not exist.\" } ] }","expUserLogsResponse"=>"{ \"data\": [ { \"old_value\": null, \"new_value\": null, \"user\": \"autouserid\", \"data_field\": null, \"created_by\": \"FOOTBALL USAFB\", \"action\": \"CREATE\" } ] }", 'key' => 'create'],
            ['TestCase' => 'verifyCreateAdminUser', 'code' => "200", "errorResponseCode" => "404", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => 'Admin User', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890','county'=>'US','organization'=>'Rising Stars'], "expResponse" => "{ \"id\": \"userID\", \"email\": \"autouser-seq@gmail.com\", \"first_name\": \"AutoFirst-seq\", \"last_name\": \"AutoLast-seq\", \"phone_number\": \"1234567890\", \"city\": \"Frisco\", \"state\": \"TX\", \"postal_code\": \"75034\", \"roles\": [ \"Admin User\" ], \"email_verified\": false, \"status\": \"Enabled\" ,\"county\": \"US\",\"organization\": \"Rising Stars\"}", "errorResponse" => "{ \"errors\":[ { \"error\": \"The user does not exist.\" } ] }", "expUserLogsResponse"=>"{ \"data\": [ { \"old_value\": null, \"new_value\": null, \"user\": \"autouserid\", \"data_field\": null, \"created_by\": \"FOOTBALL USAFB\", \"action\": \"CREATE\" } ] }",'key' => 'create'],
            ['TestCase' => 'verifyCreateUserWithEmailExists', 'code' => "409", "postBodyUser" => ['first_name' => 'AutoFirst', 'last_name' => 'AutoLast', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '', 'role' => 'Super User', 'email' => 'footballautomation@gmail.com', 'phone_number' => '1234567890','county'=>'US','organization'=>'Rising Stars'], "expResponse" => "{ \"errors\":[ { \"error\": \"The email address submitted already exists in the system.\" } ] }", 'key' => 'validations'],
            ['TestCase' => 'verifyCreateUserInvalidToken', 'code' => "401", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => 'Super User', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890','county'=>'US','organization'=>'Rising Stars'], "expResponse" => "{\"errors\":[{\"error\":\"Invalid token.\"}]}", 'key' => 'unauthorized'],
            ['TestCase' => 'verifyCreateUserNoPermission', 'code' => "403", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => 'Super User', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890','county'=>'US','organization'=>'Rising Stars'], "expResponse" => "{\"errors\":[{\"error\":\"Permission denied.\"}]}", 'key' => 'noaccess'],
            ['TestCase' => 'verifyCreateUserNoPermissionAdminUser', 'code' => "403", "postBodyUser" => ['first_name' => 'AutoFirst-seq', 'last_name' => 'AutoLast-seq', 'city' => 'Frisco', 'state' => 'TX', 'postal_code' => '75034', 'role' => 'Super User', 'email' => 'autouser-seq@gmail.com', 'phone_number' => '1234567890','county'=>'US','organization'=>'Rising Stars'], "expResponse" => "{\"errors\":[{\"error\":\"Permission denied.\"}]}", 'key' => 'adminrole']
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

    /**
     * @return array
     */
    protected function getUsers()
    {
        return [
            ['TestCase' => 'verifyListUsersWithValidLogin(Super User)', 'code' => "200", 'pageNo' => 1, 'pageSize' => 30, 'Sortable' => '+email', 'SearchString' => '', 'key' => 'valid', 'expResponse' => null],
            ['TestCase' => 'verifySearchUsersWithValidLogin(Super User)', 'code' => "200", 'pageNo' => 1, 'pageSize' => 10, 'Sortable' => '+email', 'SearchString' => 'autouser', 'key' => 'valid', 'expResponse' => ['autouser@gmail.com', 'autouser_norole@gmail.com','autouseradmin@gmail.com']]
        ];
    }

    /**
     * @return array
     */
    protected function getUsersOtherScenarios()
    {
        return [
            ['TestCase' => 'verifyListUsersWithInValidLogin(Super User)', 'code' => "403", 'pageNo' => 1, 'pageSize' => 10, 'Sortable' => '+email', 'SearchString' => '', 'key' => 'noaccess', 'expResponse' => null],
            ['TestCase' => 'verifySearchUsersWithInValidLogin(Super User)', 'code' => "403", 'pageNo' => 1, 'pageSize' => 10, 'Sortable' => '+email', 'SearchString' => 'autouser', 'key' => 'noaccess', 'expResponse' => null],
            ['TestCase' => 'verifyListUsersWithNoToken(Super User)', 'code' => "401", 'pageNo' => 1, 'pageSize' => 10, 'Sortable' => '+email', 'SearchString' => '', 'key' => 'invalid', 'expResponse' => null],
            ['TestCase' => 'verifySearchUsersWithNoToken(Super User)', 'code' => "401", 'pageNo' => 1, 'pageSize' => 10, 'Sortable' => '+email', 'SearchString' => 'autouser', 'key' => 'invalid', 'expResponse' => null],
            ['TestCase' => 'verifySearchUsersWithInvalidUser(Admin User)', 'code' => "403", 'pageNo' => 1, 'pageSize' => 10, 'Sortable' => '+email', 'SearchString' => 'autouser', 'key' => 'adminrole', 'expResponse' => null],
            ['TestCase' => 'verifyListUsersNoResults(Super User)', 'code' => "200", 'pageNo' => 2, 'pageSize' => 10, 'Sortable' => '+created_at', 'SearchString' => 'norole', 'key' => 'valid',  'expResponse' => null],
            ['TestCase' => 'verifyListUsersNoSearchNoResults(Super User)', 'code' => "200", 'pageNo' => 50, 'pageSize' => 10, 'Sortable' => '+email', 'SearchString' => '', 'key' => 'valid',  'expResponse' => null],
            ['TestCase' => 'verifySearchUsersNoResults(Super User)', 'code' => "200", 'pageNo' => 1, 'pageSize' => 10, 'Sortable' => '+email', 'SearchString' => 'random', 'key' => 'valid',  'expResponse' => null],
            ['TestCase' => 'verifyUsersSortSearchWithResultsASC(Super User)', 'code' => 200, 'pageNo' => 1, 'pageSize' => 10, 'Sortable' => '+email', 'SearchString' => 'autouser', 'key' => 'valid', 'expResponse' => ['autouser@gmail.com', 'autouser_norole@gmail.com','autouseradmin@gmail.com']],
            ['TestCase' => 'verifyUsersSortSearchWithResultsDESC(Super User)', 'code' => 200, 'pageNo' => 1, 'pageSize' => 10, 'Sortable' => '-email', 'SearchString' => 'autouser', 'key' => 'valid', 'expResponse' => ['autouseradmin@gmail.com','autouser_norole@gmail.com', 'autouser@gmail.com']]
        ];
    }

}
