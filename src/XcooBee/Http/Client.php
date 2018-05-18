<?php

namespace XcooBee\Http;

use GuzzleHttp\Client as HttpClient;
use XcooBee\Store\PersistedData;

class Client
{
    const API_URL = 'https://testapi.xcoobee.net/Test/';
    const TIME_OUT = 3000;

    public $storetoken = false;
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
        return $this->_fetchToken();
    }

    /**
     * @param array $config
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _getAuthToken($config = [])
    {
        if (!$config) {
            return $this->_fetchToken($config);
        }
        $token = PersistedData::getInstance()->getStore(PersistedData::AUTH_TOKEN_KEY);

        if($token === null){
            return $this->_fetchToken();
        }

        return $token;
    }

    /**
     * @param array $config
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _fetchToken($config = [])
    {
        if (empty($config)) {
            $config = PersistedData::getInstance()->getStore(PersistedData::CURRENT_CONFIG_KEY);
            $this->storetoken = true;
        }

        $res = $this->post($this->_getUriFromEndpoint("get_token"), ['body' => json_encode([
            'key' => $config->apiKey,
            'secret' => $config->apiSecret
        ])]);
        $token = json_decode($res->getBody());

        if ($this->storetoken === true) {
            PersistedData::getInstance()->setStore(PersistedData::AUTH_TOKEN_KEY, $token->token);
        }

        return $token->token;
    }
}
