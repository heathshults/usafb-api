<?php

namespace utils;

use ApiTester;

class CommonUtils
{

    //Method for converting Json Object to a Array Object

    public function convertJsonToArray($jsonObject)
    {
        $decodedText = html_entity_decode($jsonObject);
        $myArray = json_decode($decodedText, true);
        return $myArray;
    }


    // Method to store the Key /Value Data
    //This function can be used for comparing Expected and Actual outputs

    public function getArrayOfValue($arrayObject)
    {
        $arrayList = array();

        foreach ($arrayObject as $key => $value) {

            $arrayList[$key] = $key . ":" . $value;

        }

        return $arrayList;
    }


    // Assert Method to compare Key & Values in 2 Array Objects (Actual vs Expected)


    public function assertObjects($actualObj,$expectedObj,ApiTester $I)
    {
        foreach ($actualObj as $key => $val)

            if (array_key_exists($key, $expectedObj)) {
                $I->assertEquals($actualObj[$key], $expectedObj[$key]);
            }
    }

    // Method for generating new random number to append to entities created

    public function randomNumber()
    {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000000 + ((int)round($mt[0] * 1000000));
    }

    // Method for setting the Env Paramters at runtime

    public function setAuth0Credentials()
    {
        putenv("AUTH_CLIENT_ID=ZE6CFuU1opzEeZ5WpDzl1CZZOFrpU3T7");
        putenv("AUTH_CLIENT_SECRET=NuCNaHRUMci8OZFKCKjvZXtAq5j14NZikKLlT - Uz1UE64acsCe7y3_o3tgsAk2Y5");
        putenv("AUTH_AUDIENCE=https://daylen.auth0.com/api/v2/");
        putenv("AUTH_ISS=https://daylen.auth0.com/");
        putenv("AUTH_DOMAIN=daylen . auth0 . com");
        putenv("AUTH_METADATA=http://soccer.com/metadata");
        putenv("AUTH_CONNECTION=Username - Password - Authentication");

    }



}
