<?php

namespace XcooBee\Test;

use XcooBee\Test\TestCase;
use XcooBee\XcooBee;

abstract class IntegrationTestCase extends TestCase
{
    /** @var XcooBee */
    protected $_xcoobee;
    
    /** @var consentId */
    protected $_consentId;

    public function setup()
    {
        $this->_xcoobee = new XcooBee($this);
        $this->_xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromFile(__DIR__ . '/../../../test/integration/assets/config'));
        $consents = $this->_xcoobee->consents->listConsents();
        $this->_consentId = $consents->result->consents->data[0]->consent_cursor;
        
        parent::setUp(); 
    }
    
    protected function tearDown()
    {
        $this->_xcoobee->clearConfig();
    }
    
}