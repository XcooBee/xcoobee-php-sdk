<?php

namespace XcooBee\Test;

use XcooBee\Test\TestCase;
use XcooBee\XcooBee;

abstract class IntegrationTestCase extends TestCase
{
    /** @var XcooBee */
    protected $_xcoobee;

    const homeDir = 'F:\xampp\htdocs\xcoobee';
    
    public function setup()
    {
        $this->_xcoobee = new XcooBee($this);
        $this->_xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromFile(self::homeDir));

        parent::setUp(); 
    }
    
    protected function tearDown()
    {
        $this->_xcoobee->clearConfig();
    }
    
}