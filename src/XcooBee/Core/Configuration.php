<?php

namespace XcooBee\Core;


use XcooBee\Models\ConfigModel;
use XcooBee\Store\PersistedData;

class Configuration
{
    /**
     * Set configuration data
     *
     * @param ConfigModel $config
     */
    public function setConfig(ConfigModel $config)
    {
        PersistedData::getInstance()->setStore(PersistedData::CURRENT_CONFIG_KEY, $config);

        $currentConfig = PersistedData::getInstance()->getStore(PersistedData::CURRENT_CONFIG_KEY);
        $previousConfig = PersistedData::getInstance()->getStore(PersistedData::PREVIOUS_CONFIG_KEY);
        
        if(($currentConfig != $previousConfig) || ($previousConfig == null)) {
            PersistedData::getInstance()->setStore(PersistedData::CURRENT_CONFIG_KEY, $config);
            PersistedData::getInstance()->setStore(PersistedData::PREVIOUS_CONFIG_KEY, $config);
        }
    }

    /**
     * Get configuration
     *
     * @return ConfigModel|null
     */
    public function getConfig()
    {
        return PersistedData::getInstance()->getStore(PersistedData::CURRENT_CONFIG_KEY);
    }

    /**
     * Clears configuration from memory/cache.
     */
    public function clearConfig()
    {
        PersistedData::getInstance()->clearStore();
    }
}
