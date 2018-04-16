<?php

namespace XcooBee\Http;


use GuzzleHttp\Client as HttpClient;
use XcooBee\Store\PersistedData;

class Client
{
    const API_URL = 'https://testapi.xcoobee.net/Test/';
    const TIME_OUT = 3000;


    protected $_client;

    public function __construct()
    {
        $this->_client = new HttpClient([
            'base_uri' => self::API_URL,
            'timeout'  => self::TIME_OUT,
        ]);
    }

    /**
     * Send POST request
     *
     * @param $uri
     * @param $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($uri, $data)
    {
        return $this->_client->request('POST', $uri, $data);
    }

    /**
     * @param $endpoint
     * @return string
     */
    protected function _getUriFromEndpoint($endpoint)
    {
        return self::API_URL . $endpoint;
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _refreshAuthToken(){
        return $this->fetchToken();
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _getAuthToken()
    {
        $token = PersistedData::getInstance()->getStore(PersistedData::AUTH_TOKEN_KEY);

        if($token === null){
            return $this->fetchToken();
        }

        return $token;
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function fetchToken()
    {
        $config = PersistedData::getInstance()->getStore(PersistedData::CURRENT_CONFIG_KEY);

        $res = $this->post($this->_getUriFromEndpoint("get_token"), ['body' => json_encode([
            'key' => $config->apiKey,
            'secret' => $config->apiSecret
        ])]);
        $token = json_decode($res->getBody());

        PersistedData::getInstance()->setStore(PersistedData::AUTH_TOKEN_KEY, $token->token);

        return $token->token;
    }
}
