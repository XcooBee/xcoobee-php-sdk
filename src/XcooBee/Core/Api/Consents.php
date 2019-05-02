<?php

namespace XcooBee\Core\Api;

use XcooBee\Core\Encryption;
use XcooBee\Exception\EncryptionException;
use XcooBee\Exception\XcooBeeException;
use XcooBee\Http\Response;
use XcooBee\XcooBee;

class Consents extends Api
{
    /** @var Encryption */
    protected $_encryption;

    public function __construct(XcooBee $xcoobee)
    {
        $this->_encryption = new Encryption($xcoobee);
        parent::__construct($xcoobee);
    }

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
        $query = 'query getCampaigns($userId: String!, $first : Int, $after: String) {
            campaigns(user_cursor: $userId, first : $first , after : $after) {
                data {
                    campaign_cursor
                    campaign_name
                    status
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';

        return $this->_request($query, ['first' => $this->_getPageSize($config), 'after' => null, 'userId' => $this->_getUserId($config)], $config);
    }

    /**
     * Return information about campaign
     *
     * @param string $campaignId
     * @param array $config
     * @return Response
     * @throws XcooBeeException
     */
    public function getCampaignInfo($campaignId = null, $config = [])
    {
        $campaignId = $this->_getCampaignId($campaignId, $config);

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
     * @param string $requestRef
     * @param array $filename
     * @param string $targetUrl
     * @param string $eventHandler
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function setUserDataResponse($message, $requestRef, $filename, $targetUrl, $eventHandler, $config = [])
    {
        $mutation = 'mutation sendDataResponse($config: SendDataResponseConfig!) {
            send_data_response(config: $config) {
                ref_id
            }
        }';

        $this->_xcoobee->bees->uploadFiles([$filename], 'outbox', $config);

        return $this->_request($mutation, [
            'config' => [
                'message' => $message,
                'request_ref' => $requestRef,
                'target_url' => $targetUrl,
                'event_handler' => $eventHandler,
                'filenames' => [basename($filename)]
            ]
        ], $config);
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
    public function confirmConsentChange($consentId, $config = [])
    {
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
        $response->result = true;

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
        $response->result = true;

        return $response;
    }

    /**
     * @param string $consentId
     * @param array $config
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
                campaign_cursor,
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
    public function listConsents($statusIds = [], $config = [])
    {
        $query = 'query listConsents($userId: String!, $statuses: [ConsentStatus], $first : Int, $after: String) {
            consents(campaign_owner_cursor: $userId, statuses : $statuses, first : $first , after : $after) {
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

        $statuses = [];
        foreach ($statusIds as $statusId) {
            $statuses[] = $this->_getConsentStatus($statusId);
        }

        return $this->_request($query, [
            'statuses' => $statuses ? : null,
            'userId' => $this->_getUserId($config),
            'first' => $this->_getPageSize($config),
            'after' => null,
        ], $config);
    }

    /**
     * Query the XcooBee system for existing user consent.
     *
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

        $query = 'query listConsents($userId: String!, $campaignId: String!, $status: ConsentStatus, $data_types: [ConsentDatatype]) {
            consents(campaign_owner_cursor: $userId, campaign_cursors: [$campaignId], statuses: [$status], data_types: $data_types) {
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

        $consents = $this->_request($query, ['userId' => $this->_getUserId($config), 'campaignId' => $campaignId, 'status' => 'active', 'data_types' => ['application_cookie', 'usage_cookie', 'advertising_cookie', 'statistics_cookie']], $config);
        if ($consents->code !== 200) {
            return $consents;
        }

        $csvContent = ['application' => false, 'usage' => false, 'advertising' => false, 'statistics' => false];
        foreach ($consents->result->consents->data as $consent) {
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
        $response->result = $csvContent;

        return $response;
    }

    /**
     * Get data package.
     *
     * @param string $consentId
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function getDataPackage($consentId, $config =[])
    {
        if (!$consentId) {
            throw new XcooBeeException('No "consent" provided');
        }

        $query = 'query getDataPackage($consentId: String!) {
            data_package(consent_cursor: $consentId) {
                data
            }
        }';

        $response = $this->_request($query, ['consentId' => $consentId], $config);

        // Try to decrypt the data.
        if ($response->code === 200 && !empty($response->result->data_package->data)) {
            try {
                $decryptedData = $this->_encryption->decrypt($response->result->data_package->data);

                if ($decryptedData !== null) {
                    $response->result->data_package->data = $decryptedData;
                }
            } catch (EncryptionException $e) {
                // Do nothing, we will pass on the data as it is.
            }
        }

        return $response;
    }

    protected function _getXcoobeeIdByConsent($consentId, $config = [])
    {
        $consent = $this->getConsentData($consentId, $config = []);
        if (!empty($consent->result->consent)) {
            return $consent->result->consent->user_xcoobee_id;
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
