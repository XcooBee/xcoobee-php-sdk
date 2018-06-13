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
        $item = $this->_store->getItem($key);
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
        $item = $this->_store->getItem($key);
        return $item->get();
    }

    /**
     * Remove all data from storage
     */
    public function clearStore()
    {
        $this->_store->clear();
    }
}
