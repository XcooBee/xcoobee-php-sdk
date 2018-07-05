<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class ConsentsTest extends IntegrationTestCase
{
    /**
     * @param int $responseCode
     * @param array $responseData
     * 
     * @dataProvider CampaignsProvider
     */
    public function testListCampaigns($responseCode, $responseData)
    {
        $campaigns = $this->_xcoobee->consents->listCampaigns();
        $this->assertEquals($responseCode, $campaigns->code);
        $this->assertEquals($responseData, $campaigns->data->campaigns->data);
    }
    
    /**
     * @param int $responseCode
     * @param array $responseData
     * 
     * @dataProvider CampaignProvider
     */
    public function testGetCampaign($responseCode, $responseData)
    {
        $campaign = $this->_xcoobee->consents->getCampaign();
        $this->assertEquals($responseCode, $campaign->code);
        $this->assertEquals($responseData, $campaign->data->campaign);
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
        $this->assertEquals(true, $consent->data);
    }
    
    public function testConfirmConsentChange()
    {
        $consent = $this->_xcoobee->consents->confirmConsentChange('AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->data);
    }
    
    public function testConfirmDataDelete()
    {
        $consent = $this->_xcoobee->consents->confirmDataDelete('AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals(200, $consent->code);
        $this->assertEquals(true, $consent->data);
    }
    
    /**
     * @param int $responseCode
     * @param array $responseData
     * 
     * @dataProvider ConsentProvider
     */
    public function testGetConsentData($responseCode, $responseData)
    {
        $consent = $this->_xcoobee->consents->getConsentData('AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals($responseCode, $consent->code);
        $this->assertEquals($responseData, $consent->data->consent);
    }
    
    public function testGetCookieConsent()
    {   
        $consent = $this->_xcoobee->consents->getCookieConsent('~Volodymyr_R');
        $this->assertEquals(200, $consent->code);
    }
    
    public function CampaignsProvider()
    {
        return [[
                200,
                [(object) [
                        'campaign_name' => 'This is my other test campaign',
                        'status' => 'new'
                    ],
                    (object) [
                        'campaign_name' => 'This is my other test campaign',
                        'status' => 'new'
                    ],
                    (object) [
                        'campaign_name' => 'ganesh test camp 2',
                        'status' => 'new'
                    ],
                    (object) [
                        'campaign_name' => 'ganesh test camp 3',
                        'status' => 'new'
                    ]
                ]
            ]
        ];
    }
    
    public function CampaignProvider()
    {
        return [[
                200,
                (object) [
                        'campaign_name'     => 'ganesh test camp 2',
                        'date_c'            => '2018-04-27T12:21:22Z',
                        'date_e'            => null,
                        'status'            => 'new',
                        'xcoobee_targets'   => []
               ]
            ]
        ];
    }
    
    public function ConsentProvider()
    {
        return [[
                200,
                (object) [
                        'user_display_name'     => 'Volodymyr Rabeshko',
                        'user_xcoobee_id'       => '~Volodymyr_R',
                        'user_cursor'           => 'AvPfoQD5u4CYIJpyC7lzKHKhzp8CaIUa/JJ/VTBGWmO1QPkq1ARsy7uwQE9iJqLmVyRQKg==',
                        'consent_name'          => 'test',
                        'consent_description'   => 'test',
                        'consent_status'        => 'active',
                        'consent_type'          => 'deliver_a_product',
                        'consent_details'       => [],
                        'date_c'                => '2018-05-30T13:19:04.465Z',
                        'date_e'                => '2019-05-30T13:19:04Z',
                        'request_owner'         =>'~ganesh_',
                        'request_data_types'    => [
                            'first_name',
                            'last_name',
                            'xcoobee_id',
                            'application_cookie',
                            'usage_cookie'
                        ],
                        'required_data_types'   => [
                            'first_name',
                            'last_name',
                            'xcoobee_id',
                            'application_cookie',
                            'usage_cookie'
                        ]
               ]
            ]
        ];
    }
}
