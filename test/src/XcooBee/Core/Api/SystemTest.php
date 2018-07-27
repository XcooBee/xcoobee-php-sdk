<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;

class SystemTest extends TestCase
{
    public function testPing()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->consents = $this->_getMock(Consents::class, [
            'getCampaign' => (object) [
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
            'getCampaign' => (object) [
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
            'getCampaign' => (object) [
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
    
    /**
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider eventsSubscriptionProvider
    */
    public function testListEventSubscriptions($requestCode, $requestData, $requestError) 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError)
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['campaignId' => 'testCampaignId'], $params);
                        }));

        $response = $systemMock->listEventSubscriptions('testCampaignId');

        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($requestData, $response->data);
        $this->assertEquals($requestError, $response->errors);
    }
    
    /**
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider eventsSubscriptionProvider
    */
    public function testListEventSubscriptions_UseDefaultCampaign($requestCode, $requestData, $requestError) 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
            '_getDefaultCampaignId' => 'testCampaignId',
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['campaignId' => 'testCampaignId'], $params);
                }));

        $response = $systemMock->listEventSubscriptions();
        
        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($requestData, $response->data);
        $this->assertEquals($requestError, $response->errors);
    }

    public function testListEventSubscriptions_UseConfig() 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => $this->_createResponse(200, (object)[
                'event_subscriptions' => (object)[
                    'data' => (object) [
                        'Field' => 'testFieldValue'
                    ],
                    'page_info' => (object)[
                        'end_cursor' => 'testEndCursor',
                        'has_next_page' => null
                        
                    ]
                ]
            ])
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
    
    /**
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider addeventsProvider
    */
    public function testAddEventSubscription($requestCode, $requestData, $requestError) 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
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

        $response = $systemMock->addEventSubscription(["testEventType" => "testEventHandler"], 'testCampaignId');

        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($requestData, $response->data);
        $this->assertEquals($requestError, $response->errors);
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
    
    /**
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider addeventsProvider
    */
    public function testAddEventSubscription_UseDefaultCampaign($requestCode, $requestData, $requestError) 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
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

        $response = $systemMock->addEventSubscription(["testEventType" => "testEventHandler"]);

        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($requestData, $response->data);
        $this->assertEquals($requestError, $response->errors);
    }
    
    /**
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider addeventsProvider
    */
    public function testAddEventSubscription_useConfig($requestCode, $requestData, $requestError) 
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
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

        $response = $systemMock->addEventSubscription(["testEventType" => "testEventHandler"], 'testCampaignId', [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);

        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($requestData, $response->data);
        $this->assertEquals($requestError, $response->errors);
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
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider eventsProvider
    */
    public function testGetEvents($requestCode, $requestData, $requestError)
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
            '_getUserId' => "testUserId"
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['userId' => 'testUserId'], $params);
                        }));
        $response = $systemMock->getEvents();

        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($requestData, $response->data);
        $this->assertEquals($requestError, $response->errors);
    }
    
    /**
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider eventsProvider
    */
    public function testGetEvents_useConfig($requestCode, $requestData, $requestError)
    {
        $systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
            '_getUserId' => "testUserId"
        ]);

        $systemMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
                        }));

        $response = $systemMock->getEvents([
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);
        
        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($requestData, $response->data);
        $this->assertEquals($requestError, $response->errors);
    }
    
    public function eventsProvider()
    {
        return [
            [
                200,
                (object)[
                    'events' => (object)[
                        'data' => (object) [
                            'Field' => 'testFieldValue'
                        ],
                        'page_info' => (object)[
                            'end_cursor' => 'testEndCursor',
                            'has_next_page' => null

                        ]
                    ]
                ],
                []   
            ],
            [
                400,
                (object)[],
                ["message" => 'test error message'],
                []    
            ]
        ];
    }
    
    public function addeventsProvider()
    {
        return [
            [
                200,
                (object)[
                    'add_event_subscriptions' => (object) [
                        'data' => (object) [
                            'event_type' => 'testEventType'
                        ]
                    ]
                ],
                []   
            ],
            [
                400,
                (object)[
                    'add_event_subscriptions' => [
                        (object) []
                    ]
                ],
                ["message" => 'test error message'],
                []    
            ]
        ];
    }
    
    public function eventsSubscriptionProvider()
    {
        return [
            [
                200,
                (object)[
                    'event_subscriptions' => (object)[
                        'data' => (object) [
                            'Field' => 'testFieldValue'
                        ],
                        'page_info' => (object)[
                            'end_cursor' => 'testEndCursor',
                            'has_next_page' => null

                        ]
                    ]
                ],
                []   
            ],
            [
                400,
                (object)[],
                ["message" => 'test error message'],
                []    
            ]
        ];
    }
}
