<?php

namespace XcooBee\Core;

use XcooBee\Models\ConfigModel;
use XcooBee\Store\CachedData;
use XcooBee\XcooBee;

class Configuration
{
    /** @var XcooBee */
    protected $_xcoobee;

    /** @var ConfigModel */
    protected $_config = null;

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
        $this->_config = $config;

        $savedConfig = $this->_xcoobee->getStore()->getStore(CachedData::CONFIG_KEY);
        if($savedConfig != $config){
            $this->_xcoobee->getStore()->setStore(CachedData::CONFIG_KEY, $config);
        }
    }

    /**
     * Get configuration
     *
     * @return ConfigModel|null
     */
    public function getConfig()
    {
        if ($this->_config !== null) {
            return $this->_config;
        }

        return $this->_xcoobee->getStore()->getStore(CachedData::CONFIG_KEY);
    }

    /**
     * Clears configuration from memory/cache.
     */
    public function clearConfig()
    {
        $this->_xcoobee->getStore()->clearStore();
    }
}
