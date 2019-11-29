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
                    campaign_reference
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
     * Opens consent related dispute
     *
     * @param string $consentId
     * @param array $config
     *
     * @return Response
     *
     * @throws XcooBeeException
     */
    public function declineConsentChange($consentId, $config = [])
    {
        if (!$consentId) {
            throw new XcooBeeException('No "consent" provided');
        }

        $mutation = 'mutation declineConsentChange($consentId: String!) {
            decline_consent_change(consent_cursor: $consentId) {
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
     * @param array $filters
     * @param array $config
     *
     * @return Response
     *
     * @throws XcooBeeException
     */
    public function listConsents($filters = [], $config = [])
    {
        $query = 'query listConsents($userId: String!, $statuses: [ConsentStatus], $consentTypes: [ConsentType], $dataTypes: [ConsentDatatype], $dateFrom: String, $dateTo: String, $search: String, $country: String, $province: String, $city: String, $first: Int, $after: String) {
            consents(campaign_owner_cursor: $userId, statuses : $statuses, consent_types: $consentTypes, data_types: $dataTypes, date_from: $dateFrom, date_to: $dateTo, search: $search, country: $country, province: $province, city: $city, first: $first, after: $after) {
                data {
                    consent_cursor
                    user_cursor
                    user_display_name
                    user_xcoobee_id
                    campaign_cursor
                    campaign_status
                    consent_name
                    consent_description
                    consent_status
                    consent_type
                    consent_source
                    consent_details {
                        datatype
                        marker
                        share_hash
                    }
                    date_c
                    date_e
                    date_u
                    date_approved
                    date_deleted
                    update_confirmed
                    deletion_confirmed
                    request_data_types
                    request_data_sections {
                        section_fields {
                            datatype
                        }
                    }
                    is_data_request
                    user_email_mask
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';

        $variables = [
            'userId' => $this->_getUserId($config),
            'first' => $this->_getPageSize($config),
            'after' => null,
        ];

        if (array_key_exists('search', $filters)) {
            $variables['search'] = $filters['search'];
        }

        if (array_key_exists('country', $filters)) {
            $variables['country'] = $filters['country'];
        }

        if (array_key_exists('province', $filters)) {
            $variables['province'] = $filters['province'];
        }

        if (array_key_exists('city', $filters)) {
            $variables['city'] = $filters['city'];
        }

        if (array_key_exists('dateFrom', $filters)) {
            $dateString = $filters['dateFrom'];
            try {
                $from = new \DateTime($dateString);
                $variables['dateFrom'] = $dateString;
            } catch (\Exception $e) {
                throw new XcooBeeException("Invalid date string '$dateString' provided");
            }
        }

        if (array_key_exists('dateTo', $filters)) {
            $dateString = $filters['dateTo'];
            try {
                $from = new \DateTime($dateString);
                $variables['dateTo'] = $dateString;
            } catch (\Exception $e) {
                throw new XcooBeeException("Invalid date string '$dateString' provided");
            }
        }

        if (array_key_exists('statuses', $filters)) {
            $availableStatuses = [
                'pending',
                'active',
                'updating',
                'offer',
                'cancelled',
                'expired',
                'rejected'
            ];
            foreach ($filters['statuses'] as $status) {
                if (!in_array($status, $availableStatuses)) {
                    throw new XcooBeeException("Invalid status '$status' provided");
                }
            }

            $variables['statuses'] = $filters['statuses'];
        }

        if (array_key_exists('consentTypes', $filters)) {
            $availableTypes = [
                'missing', 'perform_contract', 'perform_a_service', 'deliver_a_product', 'order_fullfillment', 'shipping',
                'billing', 'subscription', 'support', 'support_a_service', 'support_a_product', 'warranty',
                'create_custom_service_and_product', 'create_custom_service', 'create_custom_product', 'travel',
                'deliver_itiniary_changes', 'government_services', 'emergency_services', 'law_enforcement',
                'health_care_services', 'care_delivery', 'health_billing', 'emergency_request', 'product_announcement',
                'product_information', 'survey', 'marketing', 'promotion', 'data_aggregation', 'anonymized_data_aggregation',
                'company_information', 'press_releaseases', 'financial_reports', 'website_tracking', 'web_application_tracking',
                'mobile_device_tracking', 'iot_device_tracking', 'payment_processing', 'donation', 'private_consent',
                'employee_administration', 'employee_management', 'contractor_management', 'training', 'it_administration',
                'supplier_screening', 'other',
            ];
            foreach ($filters['consentTypes'] as $type) {
                if (!in_array($type, $availableTypes)) {
                    throw new XcooBeeException("Invalid type '$type' provided");
                }
            }

            $variables['consentTypes'] = $filters['consentTypes'];
        }

        if (array_key_exists('dataTypes', $filters)) {
            $availableDataTypes = [
                'first_name', 'middle_name', 'last_name', 'name_prefix', 'name_suffix', 'xcoobee_id',
                'email', 'alternate_email', 'phone', 'alternate_phone', 'street1', 'street2', 'city',
                'state', 'postal_code', 'country', 'date_of_birth', 'image', 'ethnicity_race', 'genetic_data',
                'biometric_data', 'bank_account_description', 'bank_name', 'bank_routing_number', 'bank_swift',
                'bank_isfc', 'bank_account_number', 'bank_iban', 'paypal_email', 'payment_token',
                'government_document_references', 'government_id', 'location_data', 'health_record',
                'emergency_medical_record', 'physical_health_record', 'dental_record', 'mental_health_record',
                'health_metrics', 'internet_access_record', 'ip_address', 'device_identifiers', 'browser_details',
                'meter_reading', 'party_affiliation', 'religion', 'sexual_orientation', 'criminal_conviction', 'membership',
                'application_cookie', 'usage_cookie', 'statistics_cookie', 'advertising_cookie', 'social_posts',
                'twitter_handle', 'family_members', 'friends', 'colleagues', 'custom', 'other1', 'other2', 'other3',
                'other4', 'other5', 'other6', 'other7', 'other8', 'other9'
            ];
            foreach ($filters['dataTypes'] as $type) {
                if (!in_array($type, $availableDataTypes)) {
                    throw new XcooBeeException("Invalid data type '$type' provided");
                }
            }

            $variables['dataTypes'] = $filters['dataTypes'];
        }

        return $this->_request($query, $variables, $config);
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
        if ($response->code === 200 && !empty($response->result->data_package)) {
            foreach ($response->result->data_package as $key => $dataPackage) {
                try {
                    $decryptedData = $this->_encryption->decrypt($dataPackage->data);

                    if ($decryptedData !== null) {
                        $response->result->data_package[$key]->data = $decryptedData;
                    }
                } catch (EncryptionException $e) {
                    // Do nothing, we will pass on the data as it is.
                }
            }
        }

        return $response;
    }

    /**
     * @param string $filename
     * @param array $targets
     * @param string $reference
     * @param string $campaignId
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function registerConsents($filename = null, $targets = [], $reference = null, $campaignId = null, $config = [])
    {
        if (!$filename && !count($targets)) {
            throw new XcooBeeException('At least one of arguments [$filename, $targets] must be provided');
        }

        $campaignId = $this->_getCampaignId($campaignId, $config);

        $mutation = 'mutation registerConsents($config: RegisterConsentsConfig) {
            register_consents(config: $config) {
                ref_id
            }
        }';

        $this->_xcoobee->bees->uploadFiles([$filename], 'outbox', $config);

        return $this->_request($mutation, [
            'config' => [
                'filename' => basename($filename),
                'targets' => $targets,
                'reference' => $reference,
                'campaign_cursor' => $campaignId,
            ]
        ], $config);
    }

    /**
     * Return campaign id by it's reference
     *
     * @param string $campaignRef
     * @param array $config
     * @return Response
     * @throws XcooBeeException
     */
    public function getCampaignIdByRef($campaignRef, $config = [])
    {
        $query = 'query getCampaignId($campaignRef: String!) {
            campaign(campaign_ref: $campaignRef) {
                campaign_cursor
            }
        }';

        $campaign = $this->_request($query, ['campaignRef' => $campaignRef], $config);

        $response = new Response();
        $response->code = 200;
        $response->result = !empty($campaign->result->campaign->campaign_cursor)
            ? $campaign->result->campaign->campaign_cursor
            : null;

        return $response;
    }

    /**
     * Share consents to another campaign
     *
     * @param string $campaignRef
     * @param string $campaignId
     * @param array $consetnIds
     * @param array $config
     * @return Response
     * @throws XcooBeeException
     */
    public function shareConsents($campaignRef, $campaignId = null, $consentIds = [], $config = [])
    {
        if (!$campaignId && !$consentIds) {
            throw new XcooBeeException('Either campaignId or consentIds should be provided');
        }

        $query = 'mutation shareConsents($config: ShareConsentsConfig!){
            share_consents(config: $config){
                ref_id
            }
        }';

        return  $this->_request($query, [
            'config' => [
                'campaign_reference' => $campaignRef,
                'campaign_cursor' => $campaignId,
                'consent_cursors' => $consentIds,
            ]
        ], $config);
    }

    /**
     * Set or extend `Do Not Sell Data` flag
     *
     * @param string $email
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function dontSellData($email, $config = [])
    {
        if (!$email) {
            throw new XcooBeeException('No "email" provided');
        }

        $query = 'mutation dontSellData($email: String!){
            do_not_sell_data(email: $email){
                user_email
            }
        }';

        $response = $this->_request($query, ['email' => $email], $config);
        if ($response->code !== 200) {
            return $response;
        }

        $response = new Response();
        $response->code = 200;
        $response->result = true;

        return $response;
    }
}
