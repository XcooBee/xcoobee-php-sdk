<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Core\Api\Consents;
use XcooBee\Core\Api\System;
use XcooBee\Core\Api\Users;
use XcooBee\Core\Encryption;
use XcooBee\Exception\EncryptionException;
use XcooBee\Test\TestCase;
use XcooBee\XcooBee;

class SystemTest extends TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testPing()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->consents = $this->_getMock(Consents::class, [
            'getCampaignInfo' => (object) [
                    'result' => (object) [
                            'campaign' => (object) [
                                    'xcoobee_targets' => [],
                            ],
                    ],
            ]
        ]);
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['pgp_public_key' => 'test']
        ]);
        $systemMock = $this->_getMock(System::class, [
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
                    'result' => (object) [
                            'campaign' => (object) [
                                    'xcoobee_targets' => [],
                            ],
                    ],
            ]
        ]);
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['pgp_public_key' => 'test']
        ]);
        $systemMock = $this->_getMock(System::class, [
                '_getDefaultCampaignId' => null,
        ]);
        $XcooBeeMock->users->expects($this->once())
                ->method('getUser')
                ->will($this->returnCallback(function ($config) {
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
                }));
        $this->_setProperty($systemMock, '_xcoobee', $XcooBeeMock);
        
        $response = $systemMock->ping(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret']);
        $this->assertEquals(200, $response->code);
    }
    
    public function testPing_NoCampaignProvided()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->consents = $this->_getMock(Consents::class, [
            'getCampaignInfo' => (object) [
                    'result' => null
            ]
        ]);
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['pgp_public_key' => 'test']
        ]);
        $systemMock = $this->_getMock(System::class, [
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
        $systemMock = $this->_getMock(System::class, [
                '_getDefaultCampaignId' => null,
        ]);
        $this->_setProperty($systemMock, '_xcoobee', $XcooBeeMock);
        
        $response = $systemMock->ping();
        $this->assertEquals(400, $response->code);
        $this->assertEquals('pgp key not found.', $response->errors[0]->message);
    }
    
    public function testListEventSubscriptions() 
    {
        $systemMock = $this->_getMock(System::class, [
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
        $systemMock = $this->_getMock(System::class, [
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

    public function testListEventSubscriptions_UseConfig() 
    {
        $systemMock = $this->_getMock(System::class, [
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
        $systemMock = $this->_getMock(System::class, [
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
        $systemMock = $this->_getMock(System::class, [
            '_request' => true,
        ]);

        $systemMock->addEventSubscription(["testEventType" => "testEventHandler"], 'testCampaignId');
    }
    
    public function testAddEventSubscription_UseDefaultCampaign() 
    {
        $systemMock = $this->_getMock(System::class, [
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
        $systemMock = $this->_getMock(System::class, [
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

    public function testDeleteEventSubscription() 
    {
        $systemMock = $this->_getMock(System::class, [
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
        $systemMock = $this->_getMock(System::class, [
            '_request' => true,
        ]);

        $systemMock->deleteEventSubscription(["testEventType"], 'testCampaignId');
    }
    
    public function testDeleteEventSubscription_UseDefaultCampaign() 
    {
        $systemMock = $this->_getMock(System::class, [
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
        $systemMock = $this->_getMock(System::class, [
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

    public function testGetEvents_DecryptPayload()
    {
        $systemMock = $this->_getMock(System::class, [
            '_request' => (object)[
                'code' => 200,
                'result' => (object)[
                    'events' => (object)[
                        'data' => [
                            (object)['payload' => 'test'],
                        ],
                    ],
                ],
            ],
            '_getUserId' => "testUserId"
        ]);
        $systemMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['userId' => 'testUserId'], $params);
            }));

        $encryptionMock = $this->_getMock(Encryption::class, [
            'decrypt' => '{"test": true}',
        ]);
        $encryptionMock->expects($this->once())
            ->method('decrypt')
            ->will($this->returnCallback(function ($message) {
                $this->assertEquals('test', $message);
            }));
        $this->_setProperty($systemMock, '_encryption', $encryptionMock);

        $systemMock->getEvents();
    }

    public function testGetEvents_SkipDecryptingIfNoPGPKeyProvided()
    {
        $systemMock = $this->_getMock(System::class, [
            '_request' => (object)[
                'code' => 200,
                'result' => (object)[
                    'events' => (object)[
                        'data' => [
                            (object)['payload' => 'test'],
                        ],
                    ],
                ],
            ],
            '_getUserId' => "testUserId"
        ]);
        $systemMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['userId' => 'testUserId'], $params);
            }));

        $encryptionMock = $this->_getMock(Encryption::class, [
            'decrypt' => true,
        ]);
        $encryptionMock->expects($this->once())
            ->method('decrypt')
            ->will($this->throwException(new EncryptionException()));
        $this->_setProperty($systemMock, '_encryption', $encryptionMock);

        $eventsResponse = $systemMock->getEvents();
        $this->assertEquals($eventsResponse->result->events->data[0]->payload, "test");
    }

    public function testGetEvents_DecryptingError()
    {
        $systemMock = $this->_getMock(System::class, [
            '_request' => (object)[
                'code' => 200,
                'result' => (object)[
                    'events' => (object)[
                        'data' => [
                            (object)['payload' => 'test'],
                        ],
                    ],
                ],
            ],
            '_getUserId' => "testUserId"
        ]);
        $systemMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['userId' => 'testUserId'], $params);
            }));

        $encryptionMock = $this->_getMock(Encryption::class, [
            'decrypt' => null,
        ]);
        $this->_setProperty($systemMock, '_encryption', $encryptionMock);

        $eventsResponse = $systemMock->getEvents();
        $this->assertEquals($eventsResponse->code, 400);
        $this->assertEquals($eventsResponse->errors[0]->message, 'can\'t decrypt pgp encrypted message, check your keys');
    }

    public function testGetEvents_useConfig()
    {
        $systemMock = $this->_getMock(System::class, [
            '_request' => (object)[
                'code' => 200,
                'result' => (object)[
                    'events' => (object)[
                        'data' => [],
                    ],
                ],
            ],
            '_getUserId' => "testUserId"
        ]);

        $systemMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
            }));

        $systemMock->getEvents([
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);
    }

    public function testTriggerEvent()
    {
        $systemMock = $this->_getMock(System::class, [
            '_request' => true,
            '_getCampaignId' => 'testCampaignId',
        ]);

        $systemMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['config' => [
                    'campaign_cursor' => 'testCampaignId',
                    'type' => 'consent_approved',
                ]], $params);
            }));

        $systemMock->triggerEvent("ConsentApproved");
    }

    public function testTriggerEvent_useConfig()
    {
        $systemMock = $this->_getMock(System::class, [
            '_request' => true,
            '_getCampaignId' => 'testCampaignId',
        ]);

        $systemMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
            }));

        $systemMock->triggerEvent("ConsentApproved", [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);
    }

    /**
     * @server HTTP_XBEE_EVENT=TestEvent
     * @server HTTP_XBEE_SIGNATURE=03bb10b18a5c6e58cab5c7dc988e88a8d6870e0a
     * @server HTTP_XBEE_HANDLER=Test\XcooBee\Core\Api\SystemTest::handlerFunction
     */
    public function testHandleEvents() {
        $systemMock = $this->_getMock(System::class, []);

        $fileGetContentsMock = $this->getFunctionMock('XcooBee\Core\Api', 'file_get_contents');
        $fileGetContentsMock->expects($this->once())->willReturn('encrypted payload');

        $XcooBeeMock = $this->_getMock(XcooBee::class, []);
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['xcoobeeId' => '~test']
        ]);

        $encryptionMock = $this->_getMock(Encryption::class, [
            'decrypt' => '{"test": true}',
        ]);
        $encryptionMock->expects($this->once())
            ->method('decrypt')
            ->will($this->returnValue('{"test": true}'));

        $this->_setProperty($systemMock, '_encryption', $encryptionMock);
        $this->_setProperty($systemMock, '_xcoobee', $XcooBeeMock);

        $this->expectOutputString('{"test": true}');

        $systemMock->handleEvents();
    }

    public function testHandleEvents_withEvents() {
        $systemMock = $this->_getMock(System::class, []);

        $this->expectOutputString('encrypted payload');

        $systemMock->handleEvents([
            (object) [
                'handler'=>'Test\XcooBee\Core\Api\SystemTest::handlerFunction',
                'payload'=>'encrypted payload'
            ]
        ]);
    }

    public function handlerFunction($payload) {
       echo $payload;
    }
}

