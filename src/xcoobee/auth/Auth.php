<?php namespace XcooBee\Auth;

use XcooBee\Http\AuthClient;
use XcooBee\Store\PersistedData;
use XcooBee\Core\Configuration;
use XcooBee\Core\Constants;

class Auth
{
    /**
     * JSON Serializable Class
     *
     * @var AuthRequest
     */
    private $authRequest;

    public function refreshToken(){
        $store = new PersistedData;
        $this->fetchToken();
        return $store->getStore(Constants::AUTH_TOKEN);
    }

    public function getToken()
    {
        $store = new PersistedData;
        $token = $store->getStore(Constants::AUTH_TOKEN);
        
        if($token == null){
            $this->fetchToken();
            $token = $store->getStore(Constants::AUTH_TOKEN);
        }
        
        return $token;
    }

    private function fetchToken()
    {
        $http = new AuthClient;
        $store = new PersistedData;
        $config = $store->getStore(Constants::CURRENT_CONFIG);
        
        $authRequest = json_encode(new AuthRequest([
            'key' => $config->apiKey,
            'secret' => $config->apiSecret
        ]), JSON_PRETTY_PRINT);

        $res = $http->request("get_token", $authRequest);
        $token = json_decode($res->getBody());
        
        $store->setStore(Constants::AUTH_TOKEN, $token->token);
    }
}