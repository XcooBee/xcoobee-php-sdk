<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;

class SystemTest extends TestCase
{
    public function testPing()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->consents = $this->_getMock(Consents::class, [
            'getCampaignInfo' => (object) [
                    'data' => (object) [
                            'campaign' => (object) [
                                    'xcoobee_targets' => [],
                            ],
                    ],
            ]
        ]);
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['pgp_public_key' => 'test']
        ]);
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
                '_getDefaultCampaignId' => null,
        ]);
        $this->_setProperty($systemMock, '_xcoobee', $XcooBeeMock);
        
        $response = $systemMock->ping();
        $this->assertEquals(200, $response->code);
    }
    
    public function testPing_useConfig()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->consents = $this->_getMock(Consents::class, [
            'getCampaignInfo' => (object) [
                    'data' => (object) [
                            'campaign' => (object) [
                                    'xcoobee_targets' => [],
                            ],
                    ],
            ]
        ]);
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['pgp_public_key' => 'test']
        ]);
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
                '_getDefaultCampaignId' => null,
        ]);
        $XcooBeeMock->users->expects($this->once())
                ->method('getUser')
                ->will($this->returnCallback(function ($config) {
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
                }));
        $this->_setProperty($systemMock, '_xcoobee', $XcooBeeMock);
        
        $response = $systemMock->ping(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret']);
        $this->assertEquals(200, $response->code);
    }
    
    public function testPing_NoCampaignProvided()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->consents = $this->_getMock(Consents::class, [
            'getCampaignInfo' => (object) [
                    'data' => null
            ]
        ]);
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['pgp_public_key' => 'test']
        ]);
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
                '_getDefaultCampaignId' => null,
        ]);
        $this->_setProperty($systemMock, '_xcoobee', $XcooBeeMock);
        
        $response = $systemMock->ping();
        $this->assertEquals(400, $response->code);
        $this->assertEquals('campaign not found.', $response->errors[0]->message);
    }

    public function testPing_NoPGP()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['pgp_public_key' => null]
        ]);
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
                '_getDefaultCampaignId' => null,
        ]);
        $this->_setProperty($systemMock, '_xcoobee', $XcooBeeMock);
        
        $response = $systemMock->ping();
        $this->assertEquals(400, $response->code);
        $this->assertEquals('pgp key not found.', $response->errors[0]->message);
    }
    
    public function testListEventSubscriptions() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['campaignId' => 'testCampaignId'], $params);
                        }));

        $systemMock->listEventSubscriptions('testCampaignId');
    }

    public function testListEventSubscriptions_UseDefaultCampaign() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
            '_getDefaultCampaignId' => 'testCampaignId',
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['campaignId' => 'testCampaignId'], $params);
                }));

        $systemMock->listEventSubscriptions();
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testListEventSubscriptions_noCampaign() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
            '_getDefaultCampaignId' => null,
        ]);

        $systemMock->listEventSubscriptions();
    }

    public function testListEventSubscriptions_UseConfig() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                            $this->assertEquals(['campaignId' => 'testCampaignId'], $params);
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
                        }));

        $systemMock->listEventSubscriptions('testCampaignId', [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);
    }

    public function testAddEventSubscription() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
            '_getSubscriptionEvent' => "testEventType"
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                    $this->assertEquals(['config' => [
                            'events' => [["handler" => "testEventHandler", "event_type" => "testEventType"]],
                            'campaign_cursor' => 'testCampaignId'
                    ]], $params);
                }));

        $systemMock->addEventSubscription(["testEventType" => "testEventHandler"], 'testCampaignId');
    }
    
    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testAddEventSubscription_InvalidEvent() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
        ]);

        $systemMock->addEventSubscription(["testEventType" => "testEventHandler"], 'testCampaignId');
    }
    
    public function testAddEventSubscription_UseDefaultCampaign() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
            '_getSubscriptionEvent' => "testEventType",
            '_getDefaultCampaignId' => 'testCampaignId'
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                    $this->assertEquals(['config' => [
                            'events' => [["handler" => "testEventHandler", "event_type" => "testEventType"]],
                            'campaign_cursor' => 'testCampaignId'
                    ]], $params);
                }));

        $systemMock->addEventSubscription(["testEventType" => "testEventHandler"]);
    }

    public function testAddEventSubscription_useConfig() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
            '_getSubscriptionEvent' => "testEventType"
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                    $this->assertEquals(['config' => [
                            'events' => [["handler" => "testEventHandler", "event_type" => "testEventType"]],
                            'campaign_cursor' => 'testCampaignId'
                        ]], $params);
                    $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
                }));

        $systemMock->addEventSubscription(["testEventType" => "testEventHandler"], 'testCampaignId', [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testAddEventSubscription_noCampaign() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
            '_getSubscriptionEvent' => "testEventType",
            '_getDefaultCampaignId' => null
        ]);

        $systemMock->addEventSubscription(["testEventType" => "testEventHandler"]);
    }

    public function testDeleteEventSubscription() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
            '_getSubscriptionEvent' => "testEventType"
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                    $this->assertEquals(['config' => [
                            'events' => ["testEventType"],
                            'campaign_cursor' => 'testCampaignId'
                    ]], $params);
                }));

        $systemMock->deleteEventSubscription(["testEventType"], 'testCampaignId');
    }
    
    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testDeleteEventSubscription_InvalidEvent() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
        ]);

        $systemMock->deleteEventSubscription(["testEventType"], 'testCampaignId');
    }
    
    public function testDeleteEventSubscription_UseDefaultCampaign() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
            '_getSubscriptionEvent' => "testEventType",
            '_getDefaultCampaignId' => 'testCampaignId'
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['config' => [
                                    'events' => ["testEventType"],
                                    'campaign_cursor' => 'testCampaignId'
                                ]], $params);
                        }));

        $systemMock->deleteEventSubscription(["testEventType"]);
    }

    public function testDeleteEventSubscription_useConfig() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
            '_getSubscriptionEvent' => "testEventType"
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                    $this->assertEquals(['config' => [
                            'events' => ["testEventType"],
                            'campaign_cursor' => 'testCampaignId'
                        ]], $params);
                    $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
                }));

        $systemMock->deleteEventSubscription(["testEventType"], 'testCampaignId', [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testDeleteEventSubscription_noCampaign() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => true,
            '_getSubscriptionEvent' => "testEventType",
            '_getDefaultCampaignId' => null
        ]);

        $systemMock->deleteEventSubscription(["testEventType"]);
    }
}
