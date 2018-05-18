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
    
    public function testListCampaigns(){
        
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_request' => true,
            '_getUserId' => 'testUserID'
        ]);
        
        $consentsMock->listCampaigns();
    }
    
    public function testListCampaigns_UseConfig(){
        
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

    /**
     * @param object $consentData
     * @param string $xcoobeeId
     * @param string $referenceId
     * @param string $campaignId
     * @param string $expectedCampaignId
     * @param array $paramsExpected
     *
     * @dataProvider requestConsentProvider
     */
    public function testRequestConsent($consentData, $xcoobeeId, $referenceId, $campaignId, $expectedCampaignId, $paramsExpected)
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Consents::class, [
            '_getDefaultCampaignId' => 'testCampaignId',
            'getCampaignInfo'       => $consentData,
            'modifyCampaign'        => true,
        ]);

        $consentsMock->expects($this->once())
            ->method('modifyCampaign')
            ->will($this->returnCallback(function ($campaignId, $config) use ($expectedCampaignId, $paramsExpected) {
                $this->assertEquals($campaignId, $campaignId);
                $this->assertEquals($paramsExpected, $config);
            }));

        $consentsMock->requestConsent($xcoobeeId, $referenceId, $campaignId);
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
    
    public function requestConsentProvider() {
        return [
            [
                (object) [
                    'data' => (object) [
                        'campaign' => (object) [
                            'xcoobee_targets' => [],
                        ],
                    ],
                ],
                '~testXcoobeeId',
                null,
                'test_campaign_id',
                'test_campaign_id',
                [
                    'reference' => null,
                    'requests' => [],
                    'targets' => [
                        'xcoobee_ids' => [
                            ['xcoobee_id' => '~testXcoobeeId'],
                        ],
                    ],
                ]
            ],
            [
                (object) [
                    'data' => (object) [
                        'campaign' => (object) [
                            'xcoobee_targets' => [],
                        ],
                    ],
                ],
                '~testXcoobeeId',
                'test',
                'test_campaign_id',
                'test_campaign_id',
                [
                    'reference' => 'test',
                    'requests' => [],
                    'targets' => [
                        'xcoobee_ids' => [
                            ['xcoobee_id' => '~testXcoobeeId'],
                        ],
                    ],
                ]
            ],
            [
                (object) [
                    'data' => (object) [
                        'campaign' => (object) [
                            'xcoobee_targets' => [
                                (object) ['xcoobee_id' => '~test'],
                            ],
                        ],
                    ],
                ],
                '~testXcoobeeId',
                null,
                'test_campaign_id',
                'test_campaign_id',
                [
                    'reference' => null,
                    'requests' => [],
                    'targets' => [
                        'xcoobee_ids' => [
                            ['xcoobee_id' => '~test'],
                            ['xcoobee_id' => '~testXcoobeeId'],
                        ],
                    ],
                ]
            ],
            [
                (object) [
                    'data' => (object) [
                        'campaign' => (object) [
                            'xcoobee_targets' => [
                                (object) ['xcoobee_id' => '~test'],
                            ],
                        ],
                    ],
                ],
                '~testXcoobeeId',
                null,
                null,
                'testCampaignId',
                [
                    'reference' => null,
                    'requests' => [],
                    'targets' => [
                        'xcoobee_ids' => [
                            ['xcoobee_id' => '~test'],
                            ['xcoobee_id' => '~testXcoobeeId'],
                        ],
                    ],
                ]
            ],
        ];
    }
}
