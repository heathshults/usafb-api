<?php

namespace utils;

use ApiTester;

class CommonUtils
{

    /**
     * Function to convert json object to Array
     * @param $jsonObject
     * @return mixed
     */
    public function convertJsonToArray($jsonObject)
    {
        $decodedText = html_entity_decode($jsonObject);
        $myArray = json_decode($decodedText, true);
        return $myArray;
    }

    /**
     * Function to store key and value to a array list
     * @param $arrayObject
     * @return array
     */
    public function getArrayOfValue($arrayObject)
    {
        $arrayList = array();

        foreach ($arrayObject as $key => $value) {

            $arrayList[$key] = $key . ":" . $value;
        }
        return $arrayList;
    }


    /**
     * Function to compare actual object with expected object
     * assert actual vs expected
     * @param $actualObj
     * @param $expectedObj
     * @param ApiTester $I
     */
    public function assertObjects($actualObj, $expectedObj, ApiTester $I)
    {

        foreach ($actualObj as $key => $val)

            if (array_key_exists($key, $expectedObj)) {
                $I->assertEquals($actualObj[$key], $expectedObj[$key]);
            }
    }

    /**
     * Function to generate random number
     * @return int
     */
    public function randomNumber()
    {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000000 + ((int)round($mt[0] * 1000000));
    }

    /**
     * Set Credentials for Env File
     *
     */
    public function setAuth0Credentials()
    {
        $this->changeEnvironmentVariable("AUTH_CLIENT_ID", "MMAH4fxeYZbVMo4K9Te4jgWjkwNoqbsS");
        $this->changeEnvironmentVariable("AUTH_CLIENT_SECRET", "5yqFrD49zv-u8zY7MrstR3Mo0bDAGcpzcds099u0dr4_BateNMnjRckRjjWV8MqL");
        $this->changeEnvironmentVariable("AUTH_AUDIENCE", "https://bssauth.auth0.com/api/v2/");
        $this->changeEnvironmentVariable("AUTH_ISS", "https://bssauth.auth0.com/");
        $this->changeEnvironmentVariable("AUTH_DOMAIN", "bssauth.auth0.com");
        $this->changeEnvironmentVariable("AUTH_METADATA", "http://soccer.com/metadata");
        $this->changeEnvironmentVariable("AUTH_CONNECTION", "Username-Password-Authentication");
        $this->changeEnvironmentVariable("DB_CONNECTION", "pgsql");
        $this->changeEnvironmentVariable("DB_HOST", "postgres");
        $this->changeEnvironmentVariable("DB_PORT", "5432");
        $this->changeEnvironmentVariable("DB_DATABASE", "npdb-usafb");
        $this->changeEnvironmentVariable("DB_USERNAME", "root");
        $this->changeEnvironmentVariable("DB_PASSWORD", "root");
    }

    /**
     * Function to update .Env file parameters
     * @param $key
     * @param $value
     */
    public function changeEnvironmentVariable($key, $value)
    {
        $path = getcwd() . '/.env';

        if (file_exists($path)) {

            file_put_contents($path, str_replace(
                "$key=" . getenv($key), "$key=" . $value, file_get_contents($path)
            ));
        }
    }

    /**
     * Function to get the config values of automation setup
     * @return Settings
     */
    public function loadConfig()
    {
        $config = \Codeception\Configuration::config();
        $Settings = \Codeception\Configuration::suiteSettings('USAFBAutomationApi', $config);
        return $Settings;
    }

    /**
     * Function to get email from environment file
     * @return email
     */
    public function getEnvEmail($rolekey, ApiTester $I)
    {
        $settings = $this->loadConfig();
        if ($rolekey == "norole") {
            $email = $settings['env'][$I->getCurrentEnv()]['login']['emailNoRole'];
        } else if ($rolekey == "adminrole") {
            $email = $settings['env'][$I->getCurrentEnv()]['login']['emailadmin'];
        } else {
            $email = $settings['env'][$I->getCurrentEnv()]['login']['email'];
        }
        codecept_debug($email);
        return $email;
    }

    /**
     *  function to get password from environment file
     * @return password
     */
    public function getEnvPassword($rolekey, ApiTester $I)
    {
        $settings = $this->loadConfig();
        if ($rolekey == "norole") {
            $password = $settings['env'][$I->getCurrentEnv()]['login']['passwordNoRole'];
        } else if ($rolekey == "adminrole") {
            $password = $settings['env'][$I->getCurrentEnv()]['login']['passwordadmin'];
        } else {
            $password = $settings['env'][$I->getCurrentEnv()]['login']['password'];
        }

        return $password;
    }

    /**
     * Function for login post body to be used in tests
     * @param $databuilder
     * @param $email
     * @param $password
     * @return $postbody
     */
    public function loginPostRequest($dataBuilder, $email, $password)
    {
        if (($dataBuilder['postBody'] == null) || ($dataBuilder == null)) {
            $postbody = ['email' => $email, 'password' => $password];
        } else {
            $postbody = $dataBuilder['postBody'];
        }
        return $postbody;
    }

    /**
     * Function handles wait time between calls for Auth0.
     * @return int
     *
     */
    public function waitTimeCall()
    {
        return sleep(10);
    }

    /**
     * Function to check actual value equal to expected
     * @param $actualObj
     * @param $key
     * @param $val
     * @param ApiTester $I
     */
    public function assertEqualsKey($actualObj, $keycheck, $keyvalue, ApiTester $I)
    {
        foreach ($actualObj as $key => $val)
            if ($key == $keycheck) {
                $I->assertEquals($actualObj[$key], $keyvalue);
            }
    }

    /**
     * Function to check actual value equal to expected
     * @param $actualObj
     * @param $key
     * @param $val
     * @param ApiTester $I
     */
    public function assertNotEqualsKey($actualObj, $keycheck, $keyvalue, ApiTester $I)
    {
        foreach ($actualObj as $key => $val)
            if ($key == $keycheck) {
                $I->assertNotEquals($actualObj[$key], $keyvalue);
            }
    }

    /**
     * Function to get dsn from environment file
     * @return $dsn
     */
    public function getDsn( ApiTester $I)
    {
        $settings = $this->loadConfig();

        $dsn = $settings['env'][$I->getCurrentEnv()]['modules']['config']['Db']['dsn'];

        return $dsn;
    }

    /**
     * Function to get dbusername from environment file
     * @return dbusername
     */
    public function getDbUser( ApiTester $I)
    {
        $settings = $this->loadConfig();

        $dbUser = $settings['env'][$I->getCurrentEnv()]['modules']['config']['Db']['user'];

        return $dbUser;
    }

    /**
     * Function to get dbpassword from environment file
     * @return dbpassword
     */
    public function getDbPassword( ApiTester $I)
    {
        $settings = $this->loadConfig();

        $dbPwd = $settings['env'][$I->getCurrentEnv()]['modules']['config']['Db']['password'];

        return $dbPwd;
    }
}
