<?php

namespace XcooBee\Core;

use XcooBee\Models\ConfigModel;
use XcooBee\Store\PersistedData;
use \XcooBee\XcooBee;

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
        $this->_xcoobee->getStore()->setStore(PersistedData::CURRENT_CONFIG_KEY, $config);

        $currentConfig = $this->_xcoobee->getStore()->getStore(PersistedData::CURRENT_CONFIG_KEY);
        $previousConfig = $this->_xcoobee->getStore()->getStore(PersistedData::PREVIOUS_CONFIG_KEY);
        
        if(($currentConfig != $previousConfig) || ($previousConfig == null)) {
            $this->_xcoobee->getStore()->setStore(PersistedData::CURRENT_CONFIG_KEY, $config);
            $this->_xcoobee->getStore()->setStore(PersistedData::PREVIOUS_CONFIG_KEY, $config);
        }
    }

    /**
     * Get configuration
     *
     * @return ConfigModel|null
     */
    public function getConfig()
    {
        return $this->_xcoobee->getStore()->getStore(PersistedData::CURRENT_CONFIG_KEY);
    }

    /**
     * Clears configuration from memory/cache.
     */
    public function clearConfig()
    {
        $this->_xcoobee->getStore()->clearStore();
    }
}
