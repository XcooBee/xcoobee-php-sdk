<?php

namespace XcooBee\Core\Api;

use XcooBee\Http\GraphQLClient;
use XcooBee\Store\CachedData;
use XcooBee\XcooBee;
use XcooBee\Exception\XcooBeeException;

class Api
{
    /** @var GraphQLClient */
    protected $_client;
    
    /** @var XcooBee */
    protected $_xcoobee;
    
    public function __construct(XcooBee $xcoobee)
    {
        $this->_xcoobee = $xcoobee;
        $this->_client = new GraphQLClient($xcoobee);
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
        $currentUser = $this->_xcoobee->users->getUser($config);

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
        $store = $this->_xcoobee->getStore()->getStore(CachedData::CONFIG_KEY);

        return $store->campaignId;
    }
    
    /**
     * Get Campaign id
     * 
     * @param String $campaignId
     * @param array $config
     * 
     * @throws XcooBeeException
     */
    protected function _getCampaignId($campaignId, $config)
    {
        if($campaignId){
            return $campaignId;
        }
        if(array_key_exists('campaignId', $config)){
            return $config['campaignId'];
        }
        
        if ($campaignId = $this->_getDefaultCampaignId()) {
            return $campaignId;
        }
        
        throw new XcooBeeException('No "campaignId" provided');
    }
}
