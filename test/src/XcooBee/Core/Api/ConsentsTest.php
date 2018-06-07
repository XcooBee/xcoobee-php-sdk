<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;
use \XcooBee\Core\Api\Consents as Consent;
use \XcooBee\Http\Response;

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
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
        ]);
        $this->_setProperty($consentsMock, '_users', $this->_getMock(Users::class, [
                'sendUserMessage' => $this->_createResponse(200, true),
        ]));

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
                'sendUserMessage' => $this->_createResponse(400, true, ["error to send message"])
        ]));

        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId');
        $this->assertEquals(400, $response->code);
        $this->assertEquals("error to send message", $response->errors[0]);
    }

    public function testSetUserDataResponse_fileUpload()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getXcoobeeIdByConsent' => 'testXcoobeeId'
        ]);
        $this->_setProperty($consentsMock, '_users', $this->_getMock(Users::class, [
                'sendUserMessage' => $this->_createResponse(200, true),
        ]));
        $this->_setProperty($consentsMock, '_bees', $this->_getMock(Bees::class, [
                    'uploadFiles' => $this->_createResponse(200, true),
                    'takeOff' => $this->_createResponse(200, true),
        ]));
        $response = $consentsMock->setUserDataResponse('testMessage', 'testConsentId', 'testReference', 'testFile');
        $this->assertEquals(200, $response->code);
        $this->assertEquals(true, $response->data);
    }

    public function testSetUserDataResponse_takeOffError()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getXcoobeeIdByConsent' => 'testXcoobeeId'
        ]);
        $this->_setProperty($consentsMock, '_users', $this->_getMock(Users::class, [
                    'sendUserMessage' => $this->_createResponse(200, true)
        ]));
        $this->_setProperty($consentsMock, '_bees', $this->_getMock(Bees::class, [
                    'uploadFiles' => $this->_createResponse(200, true),
                    'takeOff' => $this->_createResponse(400, true, ["error to takeoff"])
        ]));
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

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testGetCookieConsent_noCampaign()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_getDefaultCampaignId' => null,
        ]);

        $consentsMock->getCookieConsent('testxID');
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
                                'request_data_types' => ['usage_cookie', 'advertising_cookie']
                            ],
                            (object) [
                                'consent_type' => 'test_consent_type',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['application_cookie', 'usage_cookie',]
                            ],
                            (object) [
                                'consent_type' => 'website_tracking',
                                'user_xcoobee_id' => 'demoxID',
                                'request_data_types' => ['usage_cookie']
                            ],
                        ]]
                ],
                [],
                ['application' => true, 'usage' => true, 'advertising' => true],
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
                                'request_data_types' => ['advertising_cookie']
                            ],
                            (object) [
                                'consent_type' => 'web_application_tracking',
                                'user_xcoobee_id' => 'demoxId',
                                'request_data_types' => []
                            ],
                        ]]
                ],
                [],
                ['application' => false, 'usage' => false, 'advertising' => false],
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
                ['application' => true, 'usage' => false, 'advertising' => true],
                'testxID'
            ],
            [
                200,
                (object) [
                    'consents' => (object) ['data' => [(object) [
                                'consent_type' => 'web_application_tracking',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['application_cookie', 'usage_cookie']
                            ]]]
                ],
                [],
                ['application' => true, 'usage' => true, 'advertising' => false],
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
    
    protected function _createResponse($code, $data = null, $errors = []) 
    {
        $response = new Response();
        $response->code = $code;
        $response->data = $data;
        $response->errors = $errors;

        return $response;
    }
    
}
