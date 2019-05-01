<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;
use XcooBee\Core\Api\Consents as Consent;
use XcooBee\Exception\EncryptionException;

class ConsentsTest extends TestCase
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

        $consentsMock->getCampaignInfo();
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
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
            }));

        $consentsMock->getCampaignInfo('testCampaignId', [
            'apiKey' => 'testapikey' ,
            'apiSecret' => 'testapisecret'
        ]);
    }

    public function testListCampaigns()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getUserId' => 'testUserId',
            '_getPageSize' => true,
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                    $this->assertEquals(['userId' => 'testUserId', 'first' => true, 'after' => null], $params);
            }));

        $consentsMock->listCampaigns();
    }

    public function testListCampaigns_UseConfig()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getUserId' =>'testUserId',
            '_getPageSize' => true,
        ]);
        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
        }));

        $consentsMock->listCampaigns(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret']);
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

    public function testListConsents()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getUserId' => 'testUser',
            '_getPageSize' => true,
        ]);
        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['statuses' => null, 'userId' => 'testUser', 'first' => true, 'after' => null], $params);
            }));

        $consentsMock->listConsents();
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testListConsents_invalidStatus()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getUserId' => 'testUser'
        ]);

        $consentsMock->listConsents(['testStatus']);
    }

    public function testListConsents_withStatus()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getConsentStatus' => 'testStatus',
            '_getUserId' => 'testUser',
            '_getPageSize' => true,
        ]);
        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['statuses' => ['testStatus'], 'userId' => 'testUser', 'first' => true, 'after' => null], $params);
            }));

        $consentsMock->listConsents(['testStatus']);
    }

    public function testListConsents__UseConfig()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getUserId' => 'testUser',
            '_getPageSize' => true,
        ]);
        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['statuses' => null, 'userId' => 'testUser', 'first' => true, 'after' => null], $params);
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
            }));

        $consentsMock->listConsents([], [
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
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
            }));

        $consentsMock->getConsentData('testConsentID', [
            'apiKey' => 'testapikey' ,
            'apiSecret' => 'testapisecret'
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
        $this->assertTrue($response->result);
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
        $this->assertTrue($response->result);
    }

    public function testConfirmConsentChange()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, "testData")
        ]);

        $response = $consentsMock->confirmConsentChange('testconsentId');

        $this->assertEquals(200, $response->code);
        $this->assertTrue($response->result);
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
        $this->assertTrue($response->result);
    }

    public function testSetUserDataResponse_useConfig()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->bees = $this->_getMock(Bees::class, [
            'uploadFiles' => $this->_createResponse(200, true)
        ]);

        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, "testData")
        ]);

        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['config' => [
                    'message' => 'testMessage',
                    'request_ref' => 'testReference',
                    'target_url' => 'url',
                    'event_handler' => 'handler',
                    'filenames' => ['testFile']]
                ], $params);
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
            }));
        $this->_setProperty($consentsMock, '_xcoobee', $XcooBeeMock);

        $response = $consentsMock->setUserDataResponse('testMessage', 'testReference', 'testFile', 'url', 'handler', [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]);
        $this->assertEquals(200, $response->code);
        $this->assertEquals("testData", $response->result);
    }

    public function testSetUserDataResponse_fileUpload()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->bees = $this->_getMock(Bees::class, [
            'uploadFiles' => $this->_createResponse(200, true)
        ]);
        $XcooBeeMock->bees->expects($this->once())
            ->method('uploadFiles');
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(200, true)
        ]);
        $this->_setProperty($consentsMock, '_xcoobee', $XcooBeeMock);

        $response = $consentsMock->setUserDataResponse('testMessage', 'testReference', 'testFile', 'url', 'handler');
        $this->assertEquals(200, $response->code);
        $this->assertEquals(true, $response->result);
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
        $this->assertEquals($expectedResponse, $response->result);
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
        $this->assertEquals($expectedResponse, $response->result);
        $this->assertEquals($requestError, $response->errors);
    }

    public function consentProvider()
    {
        return [
            [
                200,
                (object) [
                    'consents' => (object) [
                        'data' => [
                            (object) [
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
                        ]
                    ]
                ],
                [],
                ['application' => true, 'usage' => true, 'advertising' => true, 'statistics' => true],
                'testxID'
            ],
            [
                200,
                (object) [
                    'consents' => (object) [
                        'data' => [
                            (object) [
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
                        ]
                    ]
                ],
                [],
                ['application' => false, 'usage' => false, 'advertising' => false, 'statistics' => false],
                'demoxId'
            ],
            [
                200,
                (object) [
                    'consents' => (object) [
                        'data' => [
                            (object) [
                                'consent_type' => 'web_application_tracking',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['application_cookie', 'advertising_cookie']
                            ],
                            (object) [
                                'consent_type' => 'website_tracking',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['advertising_cookie']
                            ]
                        ]
                    ]
                ],
                [],
                ['application' => true, 'usage' => false, 'advertising' => true, 'statistics' => false],
                'testxID'
            ],
            [
                200,
                (object) [
                    'consents' => (object) [
                        'data' => [
                            (object) [
                                'consent_type' => 'web_application_tracking',
                                'user_xcoobee_id' => 'testxID',
                                'request_data_types' => ['application_cookie', 'usage_cookie', 'statistics_cookie']
                            ]
                        ]
                    ]
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

    public function testGetDataPackage()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(
                200,
                (object) [
                    'data_package' => (object) [
                        'data' => 'encrypted data package'
                    ]
                ]
            ),
        ]);

        $encryptionMock = $this->_getMock(Encryption::class, [
            'decrypt' => '{"test": true}',
        ]);
        $encryptionMock->expects($this->once())
            ->method('decrypt')
            ->will($this->returnValue('{"test": true}'));

        $this->_setProperty($consentsMock, '_encryption', $encryptionMock);

        $response = $consentsMock->getDataPackage('testConsentId');
        $this->assertEquals('{"test": true}', $response->result->data_package->data);
    }

    public function testGetDataPackage_SkipDecryptingIfNoPGPKeyProvided()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(
                200,
                (object) [
                    'data_package' => (object) [
                        'data' => 'encrypted data package'
                    ]
                ]
            ),
        ]);

        $encryptionMock = $this->_getMock(Encryption::class, [
            'decrypt' => '{"test": true}',
        ]);
        $encryptionMock->expects($this->once())
            ->method('decrypt')
            ->will($this->throwException(new EncryptionException()));

        $this->_setProperty($consentsMock, '_encryption', $encryptionMock);

        $response = $consentsMock->getDataPackage('testConsentId');
        $this->assertEquals('encrypted data package', $response->result->data_package->data);
    }

    public function testGetDataPackage_DecryptingError()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => $this->_createResponse(
                200,
                (object) [
                    'data_package' => (object) [
                        'data' => 'encrypted data package'
                    ]
                ]
            ),
        ]);

        $encryptionMock = $this->_getMock(Encryption::class, [
            'decrypt' => null,
        ]);
        $encryptionMock->expects($this->once())
            ->method('decrypt')
            ->will($this->returnValue(null));

        $this->_setProperty($consentsMock, '_encryption', $encryptionMock);

        $response = $consentsMock->getDataPackage('testConsentId');
        $this->assertEquals('encrypted data package', $response->result->data_package->data);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testGetDataPackage_NoConsentId()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, []);
        $consentsMock->getDataPackage(null);
    }
}
