<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class ConsentsTest extends IntegrationTestCase
{
    /**
     * @param int $responseData
     * 
     * @dataProvider CampaignsProvider
     */
    public function testListCampaigns($responseData)
    {
        print_r($responseData);
        $campaigns = $this->_xcoobee->consents->listCampaigns();
        $this->assertEquals(200, $campaigns->code);
        $this->assertEquals($responseData, $campaigns->data->campaigns->data);
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
    
    public function CampaignsProvider()
    {
        return [[
            [
                (object)[
                    'campaign_name' => 'This is my other test campaign',
                    'status' => 'new'
                ]
            ],
            [
                (object)[
                    'campaign_name' => 'This is my other test campaign',
                    'status' => 'new'
                ]
            ],
            [
                (object)[
                    'campaign_name' => 'ganesh test camp 2',
                    'status' => 'new'
                ]
            ],
            [
                (object)[
                    'campaign_name' => 'ganesh test camp 3',
                    'status' => 'new'
                ]
            ]
        ]
        ];
    }
}
