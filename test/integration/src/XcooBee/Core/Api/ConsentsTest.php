<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class ConsentsTest extends IntegrationTestCase
{

    public function testListCampaigns()
    {
        $campaigns = $this->_xcoobee->consents->listCampaigns();
        $this->assertEquals(200, $campaigns->code);
        $this->assertEquals('xcoobee demo campaign', $campaigns->result->campaigns->data[0]->campaign_name);
        $this->assertEquals('active', $campaigns->result->campaigns->data[0]->status);
    }

    public function testGetCampaign()
    {
        $campaign = $this->_xcoobee->consents->getCampaign();
        $this->assertEquals(200, $campaign->code);
        $this->assertEquals('xcoobee demo campaign', $campaign->result->campaign->campaign_name);
        $this->assertEquals('2018-08-03T11:39:04Z', $campaign->result->campaign->date_c);
        $this->assertEquals('active', $campaign->result->campaign->status);
        $this->assertEquals([], $campaign->result->campaign->xcoobee_targets);
    }

    public function testRequestConsent()
    {
        $consent = $this->_xcoobee->consents->requestConsent('~Ganesh_Test');
        $this->assertEquals(200, $consent->code);
    }

    public function testSetUserDataResponse()
    {
        $consent = $this->_xcoobee->consents->setUserDataResponse('test message', $this->_consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->result);
    }

    public function testConfirmConsentChange() 
    {
        $consent = $this->_xcoobee->consents->confirmConsentChange($this->_consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->result);
    }

    public function testConfirmDataDelete()
    {
        $consent = $this->_xcoobee->consents->confirmDataDelete($this->_consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->result);
    }

    public function testGetConsentData()
    {
        $consent = $this->_xcoobee->consents->getConsentData($this->_consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals('Ganesh Test', $consent->result->consent->user_display_name);
        $this->assertEquals('~Ganesh_Test', $consent->result->consent->user_xcoobee_id);
        $this->assertEquals('test', $consent->result->consent->consent_name);
        $this->assertEquals('test', $consent->result->consent->consent_description);
        $this->assertEquals('pending', $consent->result->consent->consent_status);
        $this->assertEquals([], $consent->result->consent->consent_details);
    }

    public function testGetCookieConsent()
    {
        $consent = $this->_xcoobee->consents->getCookieConsent('~demo_user');
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(['application' => false, 'usage' => false, 'advertising' => false, 'statistics' => false], $consent->result);
    }

}
