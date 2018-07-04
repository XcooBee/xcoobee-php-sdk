<?php

namespace XcooBee\Test;

use XcooBee\Test\TestCase;
use XcooBee\XcooBee;

abstract class IntegrationTestCase extends TestCase
{
    /** @var XcooBee */
    public $xcoobee;
    
    const homeDir = 'F:\xampp\htdocs\xcoobee';
    
    protected function _initXcooBee()
    {
        $this->xcoobee = new XcooBee($this);
        $this->xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromFile(self::homeDir));
    }
}