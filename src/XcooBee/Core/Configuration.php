<?php

namespace XcooBee\Core;

use XcooBee\Models\ConfigModel;
use XcooBee\Store\CachedData;
use XcooBee\XcooBee;

class Configuration
{
    /** @var XcooBee */
    protected $_xcoobee;
    
    public function __construct(XcooBee $xcoobee)
    {
        $this->_xcoobee = $xcoobee;
    }
    
    /**
     * Set configuration data
     *
     * @param ConfigModel $config
     */
    public function setConfig(ConfigModel $config)
    {
        $store = $this->_xcoobee->getStore();
        $store->setStore(CachedData::CURRENT_CONFIG_KEY, $config);

        $currentConfig = $store->getStore(CachedData::CURRENT_CONFIG_KEY);
        $previousConfig = $store->getStore(CachedData::PREVIOUS_CONFIG_KEY);
        
        if(($currentConfig != $previousConfig) || ($previousConfig == null)) {
            $store->setStore(CachedData::CURRENT_CONFIG_KEY, $config);
            $store->setStore(CachedData::PREVIOUS_CONFIG_KEY, $config);
        }
    }

    /**
     * Get configuration
     *
     * @return ConfigModel|null
     */
    public function getConfig()
    {
        return $this->_xcoobee->getStore()->getStore(CachedData::CURRENT_CONFIG_KEY);
    }

    /**
     * Clears configuration from memory/cache.
     */
    public function clearConfig()
    {
        $this->_xcoobee->getStore()->clearStore();
    }
}
