<?php 

namespace XcooBee\Core\Api;

use XcooBee\Exception\XcooBeeException;
use XcooBee\Http\Response;

class Consents extends Api
{
    
    public function __construct($xcoobee)
    {
        parent::__construct($xcoobee);
    }
    
    /**
     * List all campaigns
     *
     * @param array $config
     * 
     * @return \XcooBee\Http\Response
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listCampaigns($config = [])
    {
        $query = 'query getCampaigns($userId: String!) {
            campaigns(user_cursor: $userId) {
                data {
                    campaign_name
                    status
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';

        return $this->_request($query, ['userId' => $this->_getUserId($config)], $config);
    }

    /**
     * Return information about campaign
     * 
     * @param string $campaignId
     * @param array $config
     * @return \XcooBee\Http\Response
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCampaignInfo($campaignId = null, $config = [])
    {
        if ($campaignId === null) {
            $campaignId = $this->_getDefaultCampaignId();
        }

        if (!$campaignId) {
            throw new XcooBeeException('No "campaignId" provided');
        }

        $query = 'query getCampaignInfo($campaignId: String!) {
                campaign(campaign_cursor: $campaignId) {
                    campaign_name
                    date_c
                    date_e
                    status
                    xcoobee_targets {
                        xcoobee_id
                    }
                }
            }';

        return $this->_request($query, ['campaignId' => $campaignId], $config);
    }

    /**
     * Create campaign from passed data
     *
     * @param array $data
     * 
     * @return \XcooBee\Http\Response
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCampaign($data)
    {
        $mutation = 'mutation createCampaign($config: ConsentCampaignCreateConfig) {
                create_consent_campaign(config: $config) {
                    ref_id
                }
            }';

        return $this->_request($mutation, ['config' => $data]);
    }

    /**
     * Modify campaign with new data
     *
     * @param string $campaignId
     * @param array $data
     * 
     * @return \XcooBee\Http\Response
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function modifyCampaign($campaignId, $data)
    {
        $mutation = 'mutation modifyCampaign($config: ConsentCampaignUpdateConfig) {
                modify_consent_campaign(config: $config) {
                    ref_id
                }
            }';

        return $this->_request($mutation, ['config' => array_merge(['campaign_cursor' => $campaignId], $data)]);
    }

    /**
     * Set status of campaign to active
     *
     * @param string $campaignId
     * 
     * @return \XcooBee\Http\Response
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function activateCampaign($campaignId = null)
    {
        if ($campaignId === null) {
            $campaignId = $this->_getDefaultCampaignId();
        }

        if (!$campaignId) {
            throw new XcooBeeException('No "campaignId" provided');
        }

        $mutation = 'mutation activateCampaign($config: ActivateCampaignConfig) {
                activate_consent_campaign(config: $config) {
                    ref_id
                }
            }';

        return $this->_request($mutation, ['config' => [
            'campaign_cursor' => $campaignId,
        ]]);
    }

    /**
     * @param string $xid
     * @param string $refId
     * @param string $campaignId
     * @param string $config
     * 
     * @return \XcooBee\Http\Response
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestConsent($xid, $refId = null, $campaignId = null, $config = [])
    {
        if ($campaignId === null) {
            $campaignId = $this->_getDefaultCampaignId();
        }

        if (!$campaignId) {
            throw new XcooBeeException('No "campaignId" provided');
        }

        $mutation = 'mutation requestConsent($config: AdditionalRequestConfig) {
                send_consent_request(config: $config) {
                    ref_id
                }
            }';

        return $this->_request($mutation, ['config' => ['reference' => $refId, 'xcoobee_id' => $xid, 'campaign_cursor' => $campaignId]], $config);
    }
    
    /**
     * @param string $message
     * @param string $consentId
     * @param string $requestRef
     * @param array $filename
     * @param array $config
     * 
     * @return \XcooBee\Http\Response
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setUserDataResponse($message, $consentId, $requestRef = null, $filename = null, $config = [])
    {
        $messageResponse = $this->_xcoobee->users->sendUserMessage($message, $consentId, null, $config);
        if ($messageResponse->code !== 200) {
            return $messageResponse;
        }

        if ($requestRef && $filename) {
            $this->_xcoobee->bees->uploadFiles($filename, 'outbox', $config);
            $xcoobeeId = $this->_getXcoobeeIdByConsent($consentId, $config);
            $hireBeeResponse = $this->_xcoobee->bees->takeOff([
                'transfer' => [],
                    ], [
                'process' => [
                    'fileNames' => $filename,
                    'userReference' => $requestRef,
                    'destinations' => [$xcoobeeId],
                ],
            ], [], $config);

            if ($hireBeeResponse->code !== 200) {
                return $hireBeeResponse;
            }
        }

        $response = new Response();
        $response->code = 200;
        $response->data = true;

        return $response;
    }
    
    /**
     * confirm that data has been changed
     * 
     * @param string $consentId
     * @param array $config
     * 
     * @return \XcooBee\Http\Response
     * 
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function confirmConsentChange($consentId, $config = []) {
        if (!$consentId) {
            throw new XcooBeeException('No "consent" provided');
        }

        $mutation = 'mutation confirmConsentChange($consentId: String!) {
                confirm_consent_change(consent_cursor: $consentId) {
                    consent_cursor
                }
            }';

        $ConsentChangeResponse = $this->_request($mutation, ['consentId' => $consentId], $config);
        if ($ConsentChangeResponse->code !== 200) {
            return $ConsentChangeResponse;
        }

        $response = new Response();
        $response->code = 200;
        $response->data = true;

        return $response;
    }

    /**
     * confirm that data has been purged from company systems
     * 
     * @param string $consentId
     * @param array $config
     * 
     * @return \XcooBee\Http\Response
     * 
     * @throws XcooBeeException
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function confirmDataDelete($consentId, $config = [])
    {
        if (!$consentId) {
            throw new XcooBeeException('No "consent" provided');
        }

        $mutation = 'mutation confirmDataDelete($consentId: String!) {
                confirm_consent_deletion(consent_cursor: $consentId) {
                    consent_cursor
                }
            }';

        $confirmDataResponse = $this->_request($mutation, ['consentId' => $consentId], $config);
        if ($confirmDataResponse->code !== 200) {
            return $confirmDataResponse;
        }

        $response = new Response();
        $response->code = 200;
        $response->data = true;

        return $response;
    }
    
    /**
     * @param string $consentId
     *
     * @return \XcooBee\Http\Response
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConsentData($consentId, $config = [])
    {
        if (!$consentId) {
            throw new XcooBeeException('No "consent" provided');
        }

        $query = 'query getConsentData($consentId: String!) {
		consent(consent_cursor: $consentId) {
                    user_display_name,
                    user_xcoobee_id,
                    user_cursor,
                    consent_name,
                    consent_description,
                    consent_status,
                    consent_type,
                    consent_details {
                      datatype
                    },
                    date_c,
                    date_e,
                    request_owner,
                    request_data_types,
                    required_data_types
		}
	}';

        return $this->_request($query, ['consentId' => $consentId], $config);    
    }
    
    /**
     * query the XcooBee system for existing user consent
     * @param string $xid
     * @param string $campaignId
     * @param array $config
     * 
     * @return \XcooBee\Http\Response
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCookieConsent($xid, $campaignId = null, $config = [])
    {
        if ($campaignId === null) {
            $campaignId = $this->_getDefaultCampaignId();
        }

        if (!$campaignId) {
            throw new XcooBeeException('No "campaignId" provided');
        }

        $query = 'query listConsents($userId: String!, $campaignId: String!, $status: ConsentStatus) {
            consents(campaign_owner_cursor: $userId, campaign_cursor: $campaignId, status: $status) {
                data {
                    consent_type,
                    user_xcoobee_id,
                    request_data_types,
                    consent_status,
                    date_c
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';

        $consents = $this->_request($query, ['status' => 'active', 'userId' => $this->_getUserId($config), 'campaignId' => $campaignId], $config);
        if ($consents->code !== 200) {
            return $consents;
        }
        $csvContent = ['application' => false, 'usage' => false, 'advertising' => false];
        foreach ($consents->data->consents->data as $consent) {
            if ($xid === $consent->user_xcoobee_id && in_array($consent->consent_type, ['website_tracking', 'web_application_tracking'])) {
                if (in_array('application_cookie', $consent->request_data_types)) {
                    $csvContent['application'] = true;
                }
                if (in_array('usage_cookie', $consent->request_data_types)) {
                    $csvContent['usage'] = true;
                }
                if (in_array('advertising_cookie', $consent->request_data_types)) {
                    $csvContent['advertising'] = true;
                }
            }
        }
        $response = new Response();
        $response->code = 200;
        $response->data = $csvContent;

        return $response;
    }
    
    protected function _getXcoobeeIdByConsent($consentId, $config = []) 
    {
        $consent = $this->getConsentData($consentId, $config = []);
        if (!empty($consent->data->consent)) {
            return $consent->data->consent->user_xcoobee_id;
        }

        return false;
    }
}
