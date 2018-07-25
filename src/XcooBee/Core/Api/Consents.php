<?php 

namespace XcooBee\Core\Api;

use XcooBee\Exception\XcooBeeException;
use XcooBee\Http\Response;

class Consents extends Api
{
    /**
     * List all campaigns
     *
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
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

        $campaigns = $this->_request($query, ['userId' => $this->_getUserId($config)], $config);
        if ($campaigns->code != 200) {
            return $campaigns;
        }

        $campaigns->data->page_info = $campaigns->data->campaigns->page_info;
        $campaigns->data->campaigns = $campaigns->data->campaigns->data;

        return $campaigns;
    }

    /**
     * Return information about campaign
     * 
     * @param string $campaignId
     * @param array $config
     * @return Response
     * @throws XcooBeeException
     */
    public function getCampaign($campaignId = null, $config = [])
    {
        $campaignId = $this->_getCampaignId($campaignId, $config);
        
        $query = 'query getCampaign($campaignId: String!) {
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
     * @param string $xid
     * @param string $refId
     * @param string $campaignId
     * @param string $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function requestConsent($xid, $refId = null, $campaignId = null, $config = [])
    {
        $campaignId = $this->_getCampaignId($campaignId, $config);
        
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
     * @return Response
     * @throws XcooBeeException
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
            $hireBeeResponse = $this->_xcoobee->bees->takeOff(
                [
                    'transfer' => [],
                ], [
                    'process' => [
                        'fileNames' => $filename,
                        'userReference' => $requestRef,
                        'destinations' => [$xcoobeeId],
                    ],
                ],
                [], 
                $config);

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
     * @return Response
     * 
     * @throws XcooBeeException
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
     * @return Response
     * 
     * @throws XcooBeeException
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
     * @return Response
     * @throws XcooBeeException
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
     * list all consents
     * 
     * @param int $statusId
     * @param array $config
     * 
     * @return Response
     * 
     * @throws XcooBeeException
     */
    public function listConsents($statusId = null, $config = [])
    {
        $query = 'query listConsents($userId: String!, $statusId: ConsentStatus) {
            consents(campaign_owner_cursor: $userId, status : $statusId) {
                data {
                    consent_cursor,
                    consent_status,
                    user_xcoobee_id,
                    date_c,
                    date_e,
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';

        return $this->_request($query, [
            'statusId' => $this->_getConsentStatus($statusId),
            'userId' => $this->_getUserId($config)
        ], $config);
    }
    
    /**
     * query the XcooBee system for existing user consent
     * @param string $xid
     * @param string $campaignId
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function getCookieConsent($xid, $campaignId = null, $config = [])
    {
        $campaignId = $this->_getCampaignId($campaignId, $config);
        
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
        $csvContent = ['application' => false, 'usage' => false, 'advertising' => false, 'statistics' => false];
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
                if (in_array('statistics_cookie', $consent->request_data_types)) {
                    $csvContent['statistics'] = true;
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
    
    protected function _getConsentStatus($statusId)
    {
        if ($statusId === null) {
            return null;
        }

        $availableStatus = [
            'pending',
            'active',
            'updating',
            'offer',
            'cancelled',
            'expired',
            'rejected'
        ];
        
        if (array_key_exists($statusId, $availableStatus)) {
            return $availableStatus[$statusId];
        }

        throw new XcooBeeException('invalid "statusId" provided');
    }
}
