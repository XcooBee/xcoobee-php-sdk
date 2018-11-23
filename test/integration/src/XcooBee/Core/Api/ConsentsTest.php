<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class ConsentsTest extends IntegrationTestCase
{

    public function testListCampaigns()
    {
        $campaigns = self::$xcoobee->consents->listCampaigns();
        $this->assertEquals(200, $campaigns->code);
        $this->assertEquals('xcoobee test campaign', $campaigns->result->campaigns->data[0]->campaign_name);
        $this->assertEquals('active', $campaigns->result->campaigns->data[0]->status);
    }

    public function testGetCampaignInfo()
    {
        $campaign = self::$xcoobee->consents->getCampaignInfo();
        $this->assertEquals(200, $campaign->code);
        $this->assertEquals('xcoobee test campaign', $campaign->result->campaign->campaign_name);
        $this->assertEquals('active', $campaign->result->campaign->status);
        $this->assertEquals([], $campaign->result->campaign->xcoobee_targets);
    }

    public function testRequestConsent()
    {
        $consent = self::$xcoobee->consents->requestConsent('~Ganesh_Test');
        $this->assertEquals(200, $consent->code);
    }

    public function testSetUserDataResponse()
    {
        $consent = self::$xcoobee->consents->setUserDataResponse('test message', self::$consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->result);
    }

    public function testConfirmConsentChange() 
    {
        $consent = self::$xcoobee->consents->confirmConsentChange(self::$consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->result);
    }

    public function testConfirmDataDelete()
    {
        $consent = self::$xcoobee->consents->confirmDataDelete(self::$consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->result);
    }

    public function testGetConsentData()
    {
        $consent = self::$xcoobee->consents->getConsentData(self::$consentId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals('test', $consent->result->consent->consent_name);
        $this->assertEquals('test', $consent->result->consent->consent_description);
        $this->assertEquals('pending', $consent->result->consent->consent_status);
        $this->assertEquals([], $consent->result->consent->consent_details);
    }

    public function testGetCookieConsent()
    {
        $consent = self::$xcoobee->consents->getCookieConsent('~demo_user');
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(['application' => false, 'usage' => false, 'advertising' => false, 'statistics' => false], $consent->result);
    }

}
