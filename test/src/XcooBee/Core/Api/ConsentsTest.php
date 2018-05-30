<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;
use \XcooBee\Core\Api\Consents as Consent;

class Consents extends TestCase
{
    public function testGetCampaignInfo()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['campaignId' => 'testCampaignId'], $params);
            }));

        $consentsMock->getCampaignInfo('testCampaignId');
    }

    public function testGetCampaignInfo_UseDefaultCampaign()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getDefaultCampaignId' => 'testCampaignId',
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['campaignId' => 'testCampaignId'], $params);
            }));

        $consentsMock->getCampaignInfo();
    }
    
    public function testGetCampaignInfo_UseConfig()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['campaignId' => 'testCampaignId'], $params);
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
            }));

        $consentsMock->getCampaignInfo('testCampaignId', [
            'apiKey'=> 'testapikey' , 
            'apiSecret'=> 'testapisecret' 
        ]);
    }
    
    public function testListCampaigns()
    { 
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getUserId' => 'testUserID'
        ]);
        
        $consentsMock->listCampaigns();
    }
    
    public function testListCampaigns_UseConfig()
    {  
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getUserId'=>'testUserId'
        ]);
        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
        }));
        
        $consentsMock->listCampaigns(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret']);
    }
    
    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testGetCampaignInfo_NoCampaignProvided()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_getDefaultCampaignId' => null,
        ]);

        $consentsMock->getCampaignInfo();
    }

    public function testModifyCampaign()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['config' => ['campaign_cursor' => 'testCampaignId', 'name' => 'test']], $params);
            }));

        $consentsMock->modifyCampaign('testCampaignId', ['name' => 'test']);
    }

    public function testActivateCampaign()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['config' => ['campaign_cursor' => 'testCampaignId']], $params);
            }));

        $consentsMock->activateCampaign('testCampaignId');
    }

    public function testActivateCampaign_UseDefaultCampaign()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getDefaultCampaignId' => 'testCampaignId',
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['config' => ['campaign_cursor' => 'testCampaignId']], $params);
            }));

        $consentsMock->activateCampaign();
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testActivateCampaign_NoCampaignProvided()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_getDefaultCampaignId' => null,
        ]);

        $consentsMock->activateCampaign();
    }

    public function testRequestConsent()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['config' => ['reference' => 'testReferance', 'xcoobee_id' => '~testXcooBeeId', 'campaign_cursor' => 'testCampaignId']], $params);
            }));

        $consentsMock->requestConsent('~testXcooBeeId', 'testReferance', 'testCampaignId');
    }
    
    public function testRequestConsent_DefaultCampaign()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getDefaultCampaignId' => 'testCampaignId',
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['config' => ['reference' => 'testReferance', 'xcoobee_id' => '~testXcooBeeId', 'campaign_cursor' => 'testCampaignId']], $params);
            }));

        $consentsMock->requestConsent('~testXcooBeeId', 'testReferance', 'testCampaignId');
    }
    
    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testRequestConsent_NoCampaignProvided()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_getDefaultCampaignId' => null,
        ]);

        $consentsMock->requestConsent('test');
    }
        
    public function testGetConsentData() 
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);
        $consentsMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                    $this->assertEquals(['consentId' => 'testConsentID'], $params);
                }));

        $consentsMock->getConsentData('testConsentID');
    }
    
    public function testGetConsentData_UseConfig() 
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);
        $consentsMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                    $this->assertEquals(['consentId' => 'testConsentID'], $params);
                    $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
                }));

        $consentsMock->getConsentData('testConsentID', [
            'apiKey'=> 'testapikey' , 
            'apiSecret'=> 'testapisecret' 
        ]);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testGetConsentData_NoConsentProvided() 
    {
        $consents = new Consent();

        $consents->getConsentData(null);
    }
    
    public function testSetUserDataResponse()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);
        $this->_setProperty($consentsMock, '_users', $this->_getMock(Users::class, [
                'sendUserMessage' => (object) ['data' => true,'errors' => [],'code' => 200,
        ]]));

        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId');
        $this->assertEquals(200, $response->code);
        $this->assertEquals(true, $response->data);
    }

    public function testSetUserDataResponse_messageFailed()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);
        $this->_setProperty($consentsMock, '_users', $this->_getMock(Users::class, [
                'sendUserMessage' => (object) ['code' => 400, 'errors' => ["error to send message"]],
        ]));

        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId');
        $this->assertEquals(400, $response->code);
        $this->assertEquals("error to send message", $response->errors[0]);
    }

    public function testSetUserDataResponse_fileUpload()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);
        $this->_setProperty($consentsMock, '_users', $this->_getMock(Users::class, [
                'sendUserMessage' => (object) ['code' => 200, 'errors' => [], 'data' => true],
        ]));
        $this->_setProperty($consentsMock, '_bees', $this->_getMock(Bees::class, [
                    'uploadFiles' => (object) ['code' => 200, 'errors' => [], 'data' => true],
                    'takeOff' => (object) ['code' => 200, 'errors' => [], 'data' => true],
        ]));
        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId', 'testReference', 'testFile');
        $this->assertEquals(200, $response->code);
        $this->assertEquals(true, $response->data);
    }

    public function testSetUserDataResponse_takeOffError()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);
        $this->_setProperty($consentsMock, '_users', $this->_getMock(Users::class, [
                    'sendUserMessage' => (object) ['code' => 200, 'errors' => [], 'data' => true],
        ]));
        $this->_setProperty($consentsMock, '_bees', $this->_getMock(Bees::class, [
                    'uploadFiles' => (object) ['code' => 200, 'errors' => [], 'data' => true],
                    'takeOff' => (object) ['code' => 400, 'errors' => ["error to takeoff"]],
        ]));
        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId', 'testReference', 'testFile');
        $this->assertEquals(400, $response->code);
        $this->assertEquals("error to takeoff", $response->errors[0]);
    }
    
}
