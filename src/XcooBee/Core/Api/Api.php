<?php

namespace XcooBee\Core\Api;

use XcooBee\Http\GraphQLClient;
use XcooBee\Store\CachedData;
use XcooBee\XcooBee;
use XcooBee\Exception\XcooBeeException;

class Api
{
    const MAX_PAGE_SIZE = 100;

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
        return $this->_xcoobee->users->getUser($config)->userId;
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
        return $this->_xcoobee->getStore()->getStore(CachedData::CONFIG_KEY)->campaignId;
    }
    
    /**
     * Get Campaign id
     * 
     * @param String $campaignId
     * @param array $config
     * 
     * @return String
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
    
    /**
     * Get page size.
     *
     * @param array $config
     *
     * @return Int
     */
    protected function _getPageSize($config = [])
    {

        if (array_key_exists('pageSize', $config)) {
            return $config['pageSize'] > self::MAX_PAGE_SIZE ? self::MAX_PAGE_SIZE : $config['pageSize'];
        }

        $cachedPageSize = $this->_xcoobee->getStore()->getStore(CachedData::CONFIG_KEY)->pageSize;

        return  is_null($cachedPageSize) || $cachedPageSize > self::MAX_PAGE_SIZE ? self::MAX_PAGE_SIZE : $cachedPageSize;
    }
}
