<?php

namespace XcooBee\Http;

use GuzzleHttp\Client as HttpClient;
use XcooBee\Store\PersistedData;
use \XcooBee\XcooBee;

class Client
{
    const API_URL = 'https://testapi.xcoobee.net/Test/';
    const TIME_OUT = 3000;

    protected $_client;
	
	/** @var XcooBee */
    protected $_xcoobee;
	
    public function __construct(XcooBee $xcoobee)
    {
		$this->_xcoobee = $xcoobee;
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
     * @param array $config
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _getAuthToken($config = [])
    {
        if ($config) {
            return $this->_fetchToken($config);
        }
        
        $token = $this->_xcoobee->getStore()->getStore(PersistedData::AUTH_TOKEN_KEY);

        if($token === null){
            $config = $this->_xcoobee->getStore()->getStore(PersistedData::CURRENT_CONFIG_KEY);
            $token = $this->_fetchToken($config);
            $this->_xcoobee->getStore()->setStore(PersistedData::AUTH_TOKEN_KEY, $token);
        }

        return $token;
    }

    /**
     * @param array $config
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _fetchToken($config)
    {
        $res = $this->post($this->_getUriFromEndpoint("get_token"), ['body' => json_encode([
            'key' => $config->apiKey,
            'secret' => $config->apiSecret
        ])]);
        $token = json_decode($res->getBody());
        
        return $token->token;
    }
}
