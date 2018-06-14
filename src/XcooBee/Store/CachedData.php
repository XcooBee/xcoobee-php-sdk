<?php

namespace XcooBee\Store;

use Stash\Pool;
use Stash\Driver\FileSystem;

class CachedData
{
    const CURRENT_CONFIG_KEY = "CURRENT_CONFIG";
    const PREVIOUS_CONFIG_KEY = "PREVIOUS_CONFIG";
    const CURRENT_USER_KEY = "CURRENT_USER";
    const AUTH_TOKEN_KEY = "AUTH_TOKEN";
    const CONSENT_KEY = "CONSENT_KEY_";
    
    protected static $_instance = null;

    protected $_store;

    public function __construct()
    {
        $driver = new FileSystem([]);
        $this->_store = new Pool($driver);
    }

    /**
     * Returns instance of CachedData
     *
     * @return null|CachedData
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Setting data to storage
     *
     * @param $key
     * @param $value
     */
    public function setStore($key, $value)
    {
        $item = $this->_store->getItem(md5($key));
        $this->_store->save($item->set($value));
    }

    /**
     * Getting data from storage
     *
     * @param $key
     * @return mixed
     */
    public function getStore($key)
    {
        $item = $this->_store->getItem(md5($key));
        return $item->get();
    }

    /**
     * Remove all data from storage
     */
    public function clearStore()
    {
        $this->_store->clear();
    }
    
    /**
     * setting consent data
     *
     * @param String $consentId
     */
    public function setConsent($consentId, $consent)
    {
        $this->setStore(self::CONSENT_KEY.$consentId, $consent);
    }
    
    /**
     * Getting consent data from storage
     *
     * @param String $consentId
     * @return mixed
     */
    public function getConsent($consentId)
    {
        return $this->getStore(self::CONSENT_KEY.$consentId);
    }
}
