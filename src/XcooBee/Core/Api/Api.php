<?php

namespace XcooBee\Core\Api;

use XcooBee\Http\GraphQLClient;
use XcooBee\Store\PersistedData;
use XcooBee\Core\Configuration;

class Api
{
    /** @var GraphQLClient */
    protected $_client;

    public function __construct()
    {
        $this->_client = new GraphQLClient();
    }

    /**
     * Returns current user id
     *
     * @param array $config configuration array
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _getUserId($config = [])
    {
        $user = new Users();
        $currentUser = $user->getUser($config);

        return $currentUser->userId;
    }

    /**
     * Make request to graphQL API
     *
     * @param $query
     * @param array $variables
     * @param array $config
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _request($query, $variables = [], $config = [])
    {
        if($config){
            $config = \XcooBee\Models\ConfigModel::createFromData($config);
        }
        
        return $this->_client->request($query, $variables, [
            'Content-Type' => 'application/json',
        ], $config);
    }
    
    /**
     * Get default Campaign
     */
    protected function _getDefaultCampaignId() 
    {
        $configuration = new Configuration();

        return $configuration->getConfig()->campaignId;
    }
}
