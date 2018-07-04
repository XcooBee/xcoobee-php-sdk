<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class ConsentsTest extends IntegrationTestCase
{
    public function testListCampaigns()
    {
        $campaigns = $this->_xcoobee->consents->listCampaigns();
        $this->assertEquals(200, $campaigns->code);
    }
    
    public function testGetCampaign()
    {
        $campaign = $this->_xcoobee->consents->getCampaign();
        $this->assertEquals(200, $campaign->code);
    }
    
    public function testRequestConsent()
    {
        $consent = $this->_xcoobee->consents->requestConsent('~Volodymyr_R');
        $this->assertEquals(200, $consent->code);
    }
    
    public function testSetUserDataResponse()
    {
        $consent = $this->_xcoobee->consents->setUserDataResponse('test message', 'AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals(200, $consent->code);
    }
    
    public function testConfirmConsentChange()
    {
        $consent = $this->_xcoobee->consents->confirmConsentChange('AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals(200, $consent->code);
    }
    
    public function testConfirmDataDelete()
    {
        $consent = $this->_xcoobee->consents->confirmDataDelete('AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals(200, $consent->code);
    }
    
    public function testGetConsentData()
    {
        $consent = $this->_xcoobee->consents->getConsentData('AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals(200, $consent->code);
    }
    
    public function testGetCookieConsent()
    {   
        $consent = $this->_xcoobee->consents->getCookieConsent('~Volodymyr_R');
        $this->assertEquals(200, $consent->code);
    }
}
