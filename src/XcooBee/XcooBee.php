<?php

namespace XcooBee;

use XcooBee\Core\Configuration;
use XcooBee\Models\ConfigModel;
use XcooBee\Core\Api\System;
use XcooBee\Core\Api\Bees;
use XcooBee\Core\Api\Consents;
use XcooBee\Core\Api\Users;
use XcooBee\Core\Api\Inbox;
use XcooBee\Store\CachedData;

class XcooBee
{
    /**
     * Config Object
     *
     * @var Configuration
     */
    private $configuration;

    /** @var System */
    public $system;
    /** @var Bees */
    public $bees;
    /** @var Consents */
    public $consents;
    /** @var Users */
    public $users;
    /** @var Inbox */
    public $inbox;
    /** @var CachedData */
    private $_store;
    
    public function __construct()
    {
        $this->configuration = new Configuration($this);
        
        $this->system   = new System($this);
        $this->bees     = new Bees($this);
        $this->consents = new Consents($this);
        $this->users    = new Users($this);
        $this->inbox    = new Inbox($this);
        $this->_store   = CachedData::getInstance($this);
    }

    /**
     * Set configuration data
     *
     * @param ConfigModel $config
     */
    public function setConfig(ConfigModel $config)
    {
        $this->configuration->setConfig($config);
    }

    /**
     * Get configuration
     *
     * @return ConfigModel|null
     */
    public function getConfig()
    {
        return $this->configuration->getConfig();
    }

    /**
     * Clears configuration from memory/cache.
     */
    public function clearConfig()
    {
        $this->configuration->clearConfig();
    }
    
    /**
     * Get CachedData
     *
     * @return CachedData
     */
    public function getStore()
    {
        
        return $this->_store;
    }
}
