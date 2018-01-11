<?php

namespace App\Http\Services;

use App\Models\Provider;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Webpatser\Uuid\Uuid;

class ApiKeyService
{
    public function authenticate($request)
    {
        Log::debug('ApiKeyService - authenticate(.) called.');
        $providerId = $this->getProviderId($request);
        if (is_null($providerId)) {
            throw new UnauthorizedHttpException("Unable to determine Provider ID");
        }
        
        $provider = Provider::find($providerId);
        if (is_null($provider)) {
            throw new UnauthorizedHttpException('Unable to find Provider with ID ('.$providerId.')');
        }

        $apiKey = $provider->api_key;
        if (is_null($provider)) {
            throw new UnauthorizedHttpException('Invalid or missing Provider API Key.');
        }
            
        if ($this->validateRequest($apiKey, $request)) {
            if (is_null($providerId)) {
                throw new UnauthorizedHttpException("Unable to determine Provider ID");
            }
            Log::debug('ApiKeyService > authenticate( .. ) / Finding provider with ID: '.$providerId);
            $provider = Provider::find($providerId);
            if (is_null($provider)) {
                throw new UnauthorizedHttpException('Unable to find Provider with ID ('.$providerId.')');
            }
            Log::debug('ApiKeyService > authenticate( .. ) / Success!');
            return $provider;
        }
    }
    
    /**
     * Generates Header for API call.
     *
     * @param $jsonBody
     * @param $config
     * @return $header
     */
    public function generateAuthenticationHeaders($providerId, $body)
    {
        $timestamp = gmdate("Y-m-d\TH:i:s\Z");
        
        $provider = Provider::find($providerId);
        if (is_null($provider)) {
            throw new Exception('Unable to find Provider with ID ('.$providerId.')');
        }

        $payload = $timestamp.$body;
        $sig = base64_encode(hash_hmac('sha256', $payload, $provider->api_key, true));
        $timestampHdr = "x-usafb-timestamp";
        
        return [
            'Authorization' => "Authorization: usafb {$providerId}:{$sig}",
            'x-usafb-timestamp' => $timestamp,
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
    public function getProviderId($request)
    {
        $authHeader = $request->header('Authorization');
        if (is_null($authHeader)) {
            return null;
        }
        $re = "/(?<=usafb )(.*?)(?=:)/";
        preg_match_all($re, $authHeader, $matches, PREG_SET_ORDER, 0);
        if (count($matches) == 0) {
            return null;
        }
        return $matches[0][0];
    }

    public function validateRequest($apiKey, $request)
    {
        $authHeader = $request->header('Authorization');
        if (is_null($authHeader)) {
            return false;
        }
        
        $timestampHeader = $request->header('x-usafb-timestamp');
        if (is_null($timestampHeader)) {
            return false;
        }
        
        preg_match_all("/^(.*) (.*):(.*)$/", $authHeader, $matches, PREG_SET_ORDER, 0);
                
        if (count($matches) == 0) {
            return false;
        }
        
        $body = $request->getContent();
        $payload = $timestampHeader.$body;
        $sig = base64_encode(hash_hmac('sha256', $payload, $apiKey, true));
        return $sig == $matches[0][3];
    }
}
