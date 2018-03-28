<?php namespace xcoobee\store;
use Stash\Pool;
use Stash\Driver\FileSystem;

class PersistedData
{
    /**
   * @var \Stash\Pool
   */
    private $store;

    public function __construct()
    {
        if(null == $this->store){
            $driver = new FileSystem(array());
            $this->store = new Pool($driver);
        }
    }

    public function setStore($key, $value)
    {
        $item = $this->store->getItem($key);
        $this->store->save($item->set($value));
    }

    public function getStore($key)
    {
        $item = $this->store->getItem($key);
        return $item->get();
    }

    public function clearStore()
    {
        $this->store->clear();
    }
}