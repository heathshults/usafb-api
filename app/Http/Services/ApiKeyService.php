<?php

namespace App\Http\Services;

use Illuminate\Http\Request;

use Log;

use Webpatser\Uuid\Uuid;

class ApiKeyService
{
    const DEFAULT_ORG = "usafb";
    
    /**
     * Generates Header for API call.
     *
     * @param $jsonBody
     * @param $config
     * @return $header
     */
    public function generateHeader($jsonBody, $config)
    {
        $timestamp = gmdate("Y-m-d\TH:i:s\Z");
        $org = empty($config['org']) ? DEFAULT_ORG : $config['org'];

        if (empty($config['clientId'])) {
            trigger_error("Missing ClientID", E_USER_ERROR);
        }
        $clientId = $config['clientId'];

        if (empty($config['key'])) {
            trigger_error("Missing Key", E_USER_ERROR);
        }
        $key = $config['key'];

        $payload = $timestamp.$jsonBody;
        $sig = base64_encode(hash_hmac('sha256', $payload, $key, true));
        $timestampHdr = "x-{$org}-timestamp";
        
        return [
            'Authorization' => "Authorization: {$org} {$clientId}:{$sig}",
            $timestampHdr => $timestamp,
        ];
    }
        
    /**
     * Return new unique (API) Key
     *
     * @return $apiKey
     */
    public function generateKey()
    {
        return (string)Uuid::generate();
    }

    /**
     * Return Provider ID from authentication request header.
     *
     * @param $authHeader
     * @param $config
     * @return $companyId
     */
    public function getProviderId($authHeader, $config)
    {
        $org = empty($config['org']) ? DEFAULT_ORG : $config['org'];
        $re = "/(?<={$org} )(.*?)(?=:)/";
        preg_match_all($re, $authHeader, $matches, PREG_SET_ORDER, 0);
        return $matches[0][0];
    }

    public function validateRequest($authHeader, $timestamp, $jsonBody, $key)
    {
        $org = empty($config['org']) ? DEFAULT_ORG : $config['org'];
        $re = "/^(.*) (.*):(.*)$/";
        preg_match_all($re, $authHeader, $matches, PREG_SET_ORDER, 0);
        if (empty($config['key'])) {
            trigger_error("Missing Key", E_USER_ERROR);
        }
        $key = $config['key'];

        $payload = $timestamp.$jsonBody;
        $sig = base64_encode(hash_hmac('sha256', $payload, $key, true));

        return $sig == $matches[0][3];
    }
}
