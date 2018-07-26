<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;
use XcooBee\Core\Api\Consents as Consent;

class ConsentsTest extends TestCase
{
    public function testGetCampaign()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['campaignId' => 'testCampaignId'], $params);
            }));

        $consentsMock->getCampaign('testCampaignId');
    }

    public function testGetCampaign_UseDefaultCampaign()
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

        $consentsMock->getCampaign();
    }
    
    public function testGetCampaign_UseConfig()
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

        $consentsMock->getCampaign('testCampaignId', [
            'apiKey'=> 'testapikey' , 
            'apiSecret'=> 'testapisecret' 
        ]);
    }
    
    /**
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider campaignsProvider
    */
    public function testListCampaigns($requestCode, $requestData, $requestError)
    { 
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
            '_getUserId' => 'testUserID'
        ]);
        
        $response = $consentsMock->listCampaigns();
        
        $this->assertEquals($requestCode, $response->code);
	$this->assertEquals($requestData, $response->data);
	$this->assertEquals($requestError, $response->errors);
    }
    
    public function campaignsProvider()
    {
        return [
            [
                200,
                (object)[
                    'campaigns' => (object)[
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
    
    public function testListCampaigns_UseConfig()
    {  
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, (object)[
                'campaigns' => (object)[
                    'data' => (object) [
                        'Field' => 'testFieldValue'
                    ],
                    'page_info' => (object)[
                        'end_cursor' => 'testEndCursor',
                        'has_next_page' => null
                        
                    ]
                ]
            ]),
            '_getUserId'=>'testUserId'
        ]);
        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
        }));
        
        $consentsMock->listCampaigns(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret']);
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
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider consentsProvider
    */
    public function testListConsents($requestCode, $requestData, $requestError)
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
            '_getUserId' => 'testUser'
        ]);
        $consentsMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['statusId' => null, 'userId' => 'testUser'], $params);
                        }));

        $response = $consentsMock->listConsents();
        
        $this->assertEquals($requestCode, $response->code);
	$this->assertEquals($requestData, $response->data);
	$this->assertEquals($requestError, $response->errors);
    }
    
    public function consentsProvider()
    {
        return [
            [
                200,
                (object)[
                    'consents' => (object)[
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
    
    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testListConsents_invalidStatus()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, (object)[
                'consents' => (object)[
                    'data' => (object) [
                        'Field' => 'testFieldValue'
                    ],
                    'page_info' => (object)[
                        'end_cursor' => 'testEndCursor',
                        'has_next_page' => null
                        
                    ]
                ]
            ]),
            '_getUserId' => 'testUser'
        ]);

        $consentsMock->listConsents('testStatus');
    }

    public function testListConsents_withStatus()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, (object)[
                'consents' => (object)[
                    'data' => (object) [
                        'Field' => 'testFieldValue'
                    ],
                    'page_info' => (object)[
                        'end_cursor' => 'testEndCursor',
                        'has_next_page' => null
                        
                    ]
                ]
            ]),
            '_getConsentStatus' => 'testStatus',
            '_getUserId' => 'testUser'
        ]);
        $consentsMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['statusId' => 'testStatus', 'userId' => 'testUser'], $params);
                        }));

        $consentsMock->listConsents('testStatus');
    }

    public function testListConsents__UseConfig()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, (object)[
                'consents' => (object)[
                    'data' => (object) [
                        'Field' => 'testFieldValue'
                    ],
                    'page_info' => (object)[
                        'end_cursor' => 'testEndCursor',
                        'has_next_page' => null
                        
                    ]
                ]
            ]),
            '_getUserId' => 'testUser'
        ]);
        $consentsMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                            $this->assertEquals(['statusId' => null, 'userId' => 'testUser'], $params);
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
                        }));

        $consentsMock->listConsents(null, [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);
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
        $consents = new Consent($this->_getXcooBeeMock());

        $consents->getConsentData(null);
    }
    
    public function testConfirmDataDelete()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, "testData")
        ]);

        $consentsMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['consentId' => 'testconsentId'], $params);
                        }));

        $response = $consentsMock->confirmDataDelete('testconsentId');

        $this->assertEquals(200, $response->code);
        $this->assertTrue($response->data);
    }
    
    public function testConfirmDataDelete_onError()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(400, [], ["testError"])
        ]);

        $consentsMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['consentId' => 'testconsentId'], $params);
                        }));

        $response = $consentsMock->confirmDataDelete('testconsentId');

        $this->assertEquals(400, $response->code);
        $this->assertEquals('testError', $response->errors[0]);
    }
    
    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testConfirmDataDelete_noConsentProvided()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);

        $consentsMock->confirmDataDelete(null);
    }

    public function testConfirmDataDelete_UseConfig()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, "testData")
        ]);

        $consentsMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                            $this->assertEquals(['consentId' => 'testconsentId'], $params);
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
                        }));

        $response = $consentsMock->confirmDataDelete('testconsentId', [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);

        $this->assertEquals(200, $response->code);
        $this->assertTrue($response->data);
    }

    public function testConfirmConsentChange()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, "testData")
        ]);

        $response = $consentsMock->confirmConsentChange('testconsentId');

        $this->assertEquals(200, $response->code);
        $this->assertTrue($response->data);
    }
    
    public function testConfirmConsentChange_onError()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(400, [], ["testError"])
        ]);

        $response = $consentsMock->confirmConsentChange('testconsentId');

        $this->assertEquals(400, $response->code);
        $this->assertEquals("testError", $response->errors[0]);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testConfirmConsentChange_noConsentProvided()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);

        $consentsMock->confirmConsentChange(null);
    }

    public function testConfirmConsentChange_UseConfig()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, "testData")
        ]);

        $consentsMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                            $this->assertEquals(['consentId' => 'testconsentId'], $params);
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
                        }));

        $response = $consentsMock->confirmConsentChange('testconsentId', [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);

        $this->assertEquals(200, $response->code);
        $this->assertTrue($response->data);
    }
    
    public function testSetUserDataResponse()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'sendUserMessage' => $this->_createResponse(200, true),
        ]);
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);
        $this->_setProperty($consentsMock, '_xcoobee', $XcooBeeMock);

        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId');
        $this->assertEquals(200, $response->code);
        $this->assertEquals(true, $response->data);
    }

    public function testSetUserDataResponse_messageFailed()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'sendUserMessage' => $this->_createResponse(400, true, ["error to send message"])
        ]);
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);
        $this->_setProperty($consentsMock, '_xcoobee', $XcooBeeMock);

        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId');
        $this->assertEquals(400, $response->code);
        $this->assertEquals("error to send message", $response->errors[0]);
    }
    
    public function testSetUserDataResponse_useConfig()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'sendUserMessage' => $this->_createResponse(200, true)
        ]);
        $XcooBeeMock->bees = $this->_getMock(Bees::class, [
            'uploadFiles' => $this->_createResponse(200, true),
            'takeOff' => $this->_createResponse(200, true),
        ]);
        
        $XcooBeeMock->users->expects($this->once())
                ->method('sendUserMessage')
                ->will($this->returnCallback(function ($message, $consentId, $breach, $config) {
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
                }));
        $XcooBeeMock->bees->expects($this->once())
                ->method('uploadFiles')
                ->will($this->returnCallback(function ($files, $endpoint, $config) {
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
                }));
        $XcooBeeMock->bees->expects($this->once())
                ->method('takeOff')
                ->will($this->returnCallback(function ($bees, $options, $subscriptions, $config) {
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
                }));        
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getXcoobeeIdByConsent' => 'testXcoobeeId'
        ]);
        $this->_setProperty($consentsMock, '_xcoobee', $XcooBeeMock);
        
        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId', 'testReference', 'testFile', [
            'apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'
        ]);
        $this->assertEquals(200, $response->code);
        $this->assertEquals(true, $response->data);
    }
    
    public function testSetUserDataResponse_fileUpload()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'sendUserMessage' => $this->_createResponse(200, true)
        ]);
        $XcooBeeMock->bees = $this->_getMock(Bees::class, [
            'uploadFiles' => $this->_createResponse(200, true),
            'takeOff' => $this->_createResponse(200, true),
        ]);
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getXcoobeeIdByConsent' => 'testXcoobeeId'
        ]);
        $this->_setProperty($consentsMock, '_xcoobee', $XcooBeeMock);
        
        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId', 'testReference', 'testFile');
        $this->assertEquals(200, $response->code);
        $this->assertEquals(true, $response->data);
    }

    public function testSetUserDataResponse_takeOffError()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'sendUserMessage' => $this->_createResponse(200, true)
        ]);
        $XcooBeeMock->bees = $this->_getMock(Bees::class, [
            'uploadFiles' => $this->_createResponse(200, true),
            'takeOff' => $this->_createResponse(400, true, ["error to takeoff"]),
        ]);
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getXcoobeeIdByConsent' => 'testXcoobeeId'
        ]);
        $this->_setProperty($consentsMock, '_xcoobee', $XcooBeeMock);
        
        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId', 'testReference', 'testFile');
        $this->assertEquals(400, $response->code);
        $this->assertEquals("error to takeoff", $response->errors[0]);
    }
    
    /**
     * @param int $requestCode
     * @param array $requestData
     * @param array $requestError
     * @param array $expectedResponse
     * @param string $xid
     * 
     * @dataProvider consentProvider
     */
    public function testGetCookieConsent($requestCode, $requestData, $requestError, $expectedResponse, $xid) {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
            '_getUserId' => 'testUserID'
        ]);

        $response = $consentsMock->getCookieConsent($xid, 'testCampaignId');
        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($expectedResponse, $response->data);
        $this->assertEquals($requestError, $response->errors);
    }

    /**
     * @param int $requestCode
     * @param array $requestData
     * @param array $requestError
     * @param array $expectedResponse
     * @param string $xid
     * 
     * @dataProvider consentProvider
     */
    public function testGetCookieConsent_defaultCampaign($requestCode, $requestData, $requestError, $expectedResponse, $xid)
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
            '_getUserId' => 'testUserID',
            '_getDefaultCampaignId' => 'testCampaignId'
        ]);

        $response = $consentsMock->getCookieConsent($xid);
        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($expectedResponse, $response->data);
        $this->assertEquals($requestError, $response->errors);
    }

    public function consentProvider()
    {
        return [[
                200,
                (object) [
                    'consents' => (object) ['data' => [(object) [
                                'consent_type' => 'web_application_tracking',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['application_cookie', 'usage_cookie', 'advertising_cookie']
                            ],
                            (object) [
                                'consent_type' => 'website_tracking',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['usage_cookie', 'advertising_cookie', 'statistics_cookie']
                            ],
                            (object) [
                                'consent_type' => 'test_consent_type',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['application_cookie', 'usage_cookie', 'statistics_cookie']
                            ],
                            (object) [
                                'consent_type' => 'website_tracking',
                                'user_xcoobee_id' => 'demoxID',
                                'request_data_types' => ['usage_cookie']
                            ],
                        ]]
                ],
                [],
                ['application' => true, 'usage' => true, 'advertising' => true, 'statistics' => true],
                'testxID'
            ],
            [
                200,
                (object) [
                    'consents' => (object) ['data' => [(object) [
                                'consent_type' => 'website_tracking',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['usage_cookie']
                            ],
                            (object) [
                                'consent_type' => 'test_consent_type',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['advertising_cookie', 'statistics_cookie']
                            ],
                            (object) [
                                'consent_type' => 'web_application_tracking',
                                'user_xcoobee_id' => 'demoxId',
                                'request_data_types' => []
                            ],
                        ]]
                ],
                [],
                ['application' => false, 'usage' => false, 'advertising' => false, 'statistics' => false],
                'demoxId'
            ],
            [
                200,
                (object) [
                    'consents' => (object) ['data' => [(object) [
                                'consent_type' => 'web_application_tracking',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['application_cookie', 'advertising_cookie']
                            ],
                            (object) [
                                'consent_type' => 'website_tracking',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['advertising_cookie']
                            ]
                        ]]
                ],
                [],
                ['application' => true, 'usage' => false, 'advertising' => true, 'statistics' => false],
                'testxID'
            ],
            [
                200,
                (object) [
                    'consents' => (object) ['data' => [(object) [
                                'consent_type' => 'web_application_tracking',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['application_cookie', 'usage_cookie', 'statistics_cookie']
                            ]]]
                ],
                [],
                ['application' => true, 'usage' => true, 'advertising' => false, 'statistics' => true],
                'testxID'
            ],
            [
                400,
                [],
                ['testError'],
                [],
                'testxID'
            ]
        ];
    }
    
}
