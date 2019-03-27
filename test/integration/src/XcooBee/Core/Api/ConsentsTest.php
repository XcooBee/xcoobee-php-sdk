<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class ConsentsTest extends IntegrationTestCase
{

    public function testListCampaigns()
    {
        $campaigns = self::$xcoobee->consents->listCampaigns();
        $this->assertEquals(200, $campaigns->code);
        $this->assertEquals('Test campaign', $campaigns->result->campaigns->data[0]->campaign_name);
        $this->assertEquals('active', $campaigns->result->campaigns->data[0]->status);
    }

    public function testGetCampaignInfo()
    {
        $campaign = self::$xcoobee->consents->getCampaignInfo();
        $this->assertEquals(200, $campaign->code);
        $this->assertEquals('Test campaign', $campaign->result->campaign->campaign_name);
        $this->assertEquals('active', $campaign->result->campaign->status);
        $this->assertEquals([], $campaign->result->campaign->xcoobee_targets);
    }

    public function testRequestConsent()
    {
        $user = self::$xcoobee->users->getUser();
        $consent = self::$xcoobee->consents->requestConsent($user->xcoobeeId);
        $this->assertEquals(200, $consent->code);
    }

    public function testSetUserDataResponse()
    {
        $consent = self::$xcoobee->consents->setUserDataResponse('test message', 'requestRef', __DIR__ . '/../../../../assets/testfile.txt');
        $this->assertEquals(400, $consent->code);
        $this->assertEquals('Data request not found', $consent->errors[0]->message);
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
        $this->assertEquals('Campaign title', $consent->result->consent->consent_name);
        $this->assertEquals('Test campaign description', $consent->result->consent->consent_description);
        $this->assertEquals([], $consent->result->consent->consent_details);
    }

    public function testGetCookieConsent()
    {
        $user = self::$xcoobee->users->getUser();
        $consent = self::$xcoobee->consents->getCookieConsent($user->xcoobeeId);
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(['application' => false, 'usage' => false, 'advertising' => false, 'statistics' => false], $consent->result);
    }

}
