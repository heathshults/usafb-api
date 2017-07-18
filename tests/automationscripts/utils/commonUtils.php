<?php

namespace utils;

use ApiTester;

class CommonUtils
{

    /*
     *Method to converting JSON to Array
     */

    public function convertJsonToArray($jsonObject)
    {
        $decodedText = html_entity_decode($jsonObject);
        $myArray = json_decode($decodedText, true);
        return $myArray;
    }


    /*
    * Method to store Key and Value in Array
   */
    public function getArrayOfValue($arrayObject)
    {
        $arrayList = array();

        foreach ($arrayObject as $key => $value) {

            $arrayList[$key] = $key . ":" . $value;

        }

        return $arrayList;
    }


    /*
   *  Method  to assert actual vs expected
   */
    public function assertObjects($actualObj, $expectedObj, ApiTester $I)
    {

        foreach ($actualObj as $key => $val)

            if (array_key_exists($key, $expectedObj)) {
                $I->assertEquals($actualObj[$key], $expectedObj[$key]);
            }
    }


    /*
    * Method for generating random number
    */

    public function randomNumber()
    {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000000 + ((int)round($mt[0] * 1000000));
    }


    /*
   * Method for setting env Paramters
   */

    public function setAuth0Credentials()
    {
        $this->changeEnvironmentVariable("AUTH_CLIENT_ID", "ZE6CFuU1opzEeZ5WpDzl1CZZOFrpU3T7");
        $this->changeEnvironmentVariable("AUTH_CLIENT_SECRET", "NuCNaHRUMci8OZFKCKjvZXtAq5j14NZikKLlT-Uz1UE64acsCe7y3_o3tgsAk2Y5");
        $this->changeEnvironmentVariable("AUTH_AUDIENCE", "https://daylen.auth0.com/api/v2/");
        $this->changeEnvironmentVariable("AUTH_ISS", "https://daylen.auth0.com/");
        $this->changeEnvironmentVariable("AUTH_DOMAIN", "daylen.auth0.com");
        $this->changeEnvironmentVariable("AUTH_METADATA", "http://soccer.com/metadata");
        $this->changeEnvironmentVariable("AUTH_CONNECTION", "Username-Password-Authentication");

    }


    /*
     * Method to write env params to Env file
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


}
