<?php

namespace Test\XcooBee\Store;


use XcooBee\Store\CachedData as Store;
use XcooBee\Test\TestCase;

class CachedData extends TestCase
{
    protected function tearDown()
    {
        Store::getInstance()->clearStore();

        parent::tearDown();
    }

    public function testSetStore()
    {
        Store::getInstance()->setStore('key', 'value');
        $this->assertEquals('value', Store::getInstance()->getStore('key'));
    }

    public function testSetStore_StoreIsNull()
    {
        $store = new Store();
        $this->assertEquals(null, $store->getStore('key'));
    }

    public function testGetStore()
    {
        $this->assertNull(Store::getInstance()->getStore('key'));
        Store::getInstance()->setStore('key', 'value');
        $this->assertEquals('value', Store::getInstance()->getStore('key'));
    }

    public function testGetStore_StoreIsNull()
    {
        $store = new Store();
        $this->assertEquals(null, $store->getStore('key', 'value'));
    }

    public function testClearStore()
    {
        Store::getInstance()->setStore('key', 'value');
        Store::getInstance()->setStore('key2', 'value2');
        $this->assertEquals('value', Store::getInstance()->getStore('key'));
        $this->assertEquals('value2', Store::getInstance()->getStore('key2'));
        Store::getInstance()->clearStore();
        $this->assertNull(Store::getInstance()->getStore('key'));
        $this->assertNull(Store::getInstance()->getStore('key2'));
    }

    public function testClearStore_StoreIsNull()
    {
        $store = new Store();
        $this->assertEquals(null, $store->getStore('key', 'value'));
        $this->assertEquals(null, $store->getStore('key'));
    }
}
