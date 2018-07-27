<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class ConsentsTest extends IntegrationTestCase
{

    public function testListCampaigns()
    {
        $campaigns = $this->_xcoobee->consents->listCampaigns();
        $this->assertEquals(200, $campaigns->code);
        $this->assertEquals('This is my other test campaign', $campaigns->data->campaigns[0]->campaign_name);
        $this->assertEquals('new', $campaigns->data->campaigns[0]->status);
    }

    public function testGetCampaign()
    {
        $campaign = $this->_xcoobee->consents->getCampaign();
        $this->assertEquals(200, $campaign->code);
        $this->assertEquals('ganesh test camp 2', $campaign->data->campaign->campaign_name);
        $this->assertEquals('2018-04-27T12:21:22Z', $campaign->data->campaign->date_c);
        $this->assertEquals('new', $campaign->data->campaign->status);
        $this->assertEquals([], $campaign->data->campaign->xcoobee_targets);
    }

    public function testRequestConsent()
    {
        $consent = $this->_xcoobee->consents->requestConsent('~demo_user');
        $this->assertEquals(200, $consent->code);
    }

    public function testSetUserDataResponse()
    {
        $consent = $this->_xcoobee->consents->setUserDataResponse('test message', $this->_consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->data);
    }

    public function testConfirmConsentChange() 
    {
        $consent = $this->_xcoobee->consents->confirmConsentChange($this->_consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->data);
    }

    public function testConfirmDataDelete()
    {
        $consent = $this->_xcoobee->consents->confirmDataDelete($this->_consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->data);
    }

    public function testGetConsentData()
    {
        $consent = $this->_xcoobee->consents->getConsentData($this->_consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals('Test User', $consent->data->consent->user_display_name);
        $this->assertEquals('~demo_user', $consent->data->consent->user_xcoobee_id);
        $this->assertEquals('test', $consent->data->consent->consent_name);
        $this->assertEquals('test', $consent->data->consent->consent_description);
        $this->assertEquals('pending', $consent->data->consent->consent_status);
        $this->assertEquals([], $consent->data->consent->consent_details);
    }

    public function testGetCookieConsent()
    {
        $consent = $this->_xcoobee->consents->getCookieConsent('~demo_user');
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(['application' => false, 'usage' => false, 'advertising' => false, 'statistics' => false], $consent->data);
    }

}
