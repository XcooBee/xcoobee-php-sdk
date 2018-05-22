<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;

class SystemTest extends TestCase
{
    public function testPing()
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
                '_getDefaultCampaignId' => null,
        ]);
        $this->_setProperty($systemMock, '_users', $this->_getMock(Users::class, [
                'getUser' => (object) ['pgp_public_key' => 'test']
        ]));
        $this->_setProperty($systemMock, '_consent', $this->_getMock(Users::class, [
                'getCampaignInfo' => (object) [
                        'data' => (object) [
                                'campaign' => (object) [
                                        'xcoobee_targets' => [],
                                ],
                        ],
                ]
        ]));

        $response = $systemMock->ping();
        $this->assertEquals(200, $response->code);
    }

    public function testPing_NoCampaignProvided()
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
                '_getDefaultCampaignId' => null,
        ]);
        $this->_setProperty($systemMock, '_users', $this->_getMock(Users::class, [
                'getUser' => (object) ['pgp_public_key' => 'test']
        ]));
        $this->_setProperty($systemMock, '_consent', $this->_getMock(Users::class, [
                'getCampaignInfo' => (object) [
                        'data' => null
                ]
        ]));

        $response = $systemMock->ping();
        $this->assertEquals(400, $response->code);
        $this->assertEquals('campaign not found.', $response->errors[0]->message);
    }

    public function testPing_NoPGP()
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
                '_getDefaultCampaignId' => null,
        ]);
        $this->_setProperty($systemMock, '_users', $this->_getMock(Users::class, [
                'getUser' => (object) ['pgp_public_key' => null]
        ]));

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
