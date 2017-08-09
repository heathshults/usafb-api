<?php

namespace utils;

use ApiTester;

class CommonUtils
{

    /**
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
     * random number generation
     * @return int
     */
    public function randomNumber()
    {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000000 + ((int)round($mt[0] * 1000000));
    }


    /**
     * Set Credentials for Env File
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
     * @param $key
     * @param $value
     */
    public function changeEnvironmentVariable($key, $value)
    {
        $path = getcwd().'/.env';

        if (file_exists($path)) {

            file_put_contents($path, str_replace(
                "$key=".getenv($key), "$key=".$value, file_get_contents($path)
            ));
        }
    }


    /**
     * @return Settings
     */
    public function loadConfig()
    {
        $config = \Codeception\Configuration::config();
        $Settings = \Codeception\Configuration::suiteSettings('USAFBAutomationApi', $config);
        return $Settings;
    }


    /**
     * @return email
     */
    public function getEnvEmail($noRole,ApiTester $I)
    {
        $settings = $this->loadConfig();
        if ($noRole != "")

            $email = $settings['env'][$I->getCurrentEnv()]['login']['emailNoRole'];
        else
            $email = $settings['env'][$I->getCurrentEnv()]['login']['email'];
        return $email;
    }

    /**
     * @return password
     */
    public function getEnvPassword($noRole,ApiTester $I)
    {

        $settings = $this->loadConfig();

        if ($noRole != "")
            $email = $settings['env'][$I->getCurrentEnv()]['login']['passwordNoRole'];
        else
            $email = $settings['env'][$I->getCurrentEnv()]['login']['password'];
        return $email;
    }


    /**
     * @param $databuilder
     * @param $email
     * @param $password
     * @return $postbody
     */
    public function loginPostRequest($dataBuilder, $email, $password)
    {
        if (($dataBuilder['postBody'] == null) || ($dataBuilder ==null)) {
            $postbody = ['email' => $email, 'password' => $password];
        } else {
            $postbody = $dataBuilder['postBody'];
        }
        return $postbody;
    }
}
