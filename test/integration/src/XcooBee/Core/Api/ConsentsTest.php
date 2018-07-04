<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class ConsentsTest extends IntegrationTestCase
{
    public function testGetConsent()
    {
        $this->_initXcooBee();
        $campaigns = $this->xcoobee->consents->listCampaigns();
        $this->assertEquals(200, $campaigns->code);
    }
    
    public function testGetCampaign()
    {
        $this->_initXcooBee();
        $campaign = $this->xcoobee->consents->getCampaign();
        $this->assertEquals(200, $campaign->code);
    }
    
    public function testRequestConsent()
    {
        $this->_initXcooBee();
        $consent = $this->xcoobee->consents->requestConsent('~Volodymyr_R');
        $this->assertEquals(200, $consent->code);
    }
    
    public function testSetUserDataResponse()
    {
        $this->_initXcooBee();
        $consent = $this->xcoobee->consents->setUserDataResponse('test message', 'AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals(200, $consent->code);
    }
    
    public function testConfirmConsentChange()
    {
        $this->_initXcooBee();
        $consent = $this->xcoobee->consents->confirmConsentChange('AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals(200, $consent->code);
    }
    
    public function testConfirmDataDelete()
    {
        $this->_initXcooBee();
        $consent = $this->xcoobee->consents->confirmDataDelete('AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals(200, $consent->code);
    }
    
    public function testGetConsentData()
    {
        $this->_initXcooBee();
        $consent = $this->xcoobee->consents->getConsentData('AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals(200, $consent->code);
    }
    
    public function testGetCookieConsent()
    {   
        $this->_initXcooBee();
        $consent = $this->xcoobee->consents->getCookieConsent('~Volodymyr_R');
        $this->assertEquals(200, $consent->code);
    }
}
