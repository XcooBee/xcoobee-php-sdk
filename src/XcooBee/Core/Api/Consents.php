<?php 

namespace XcooBee\Core\Api;

use XcooBee\Core\Configuration;
use XcooBee\Exception\XcooBeeException;

class Consents extends Api
{
    /**
     * List all campaigns
     *
     * @param array $config
     * 
     * @return \XcooBee\Http\Response
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
     * @return \XcooBee\Http\Response
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
     * @return \XcooBee\Http\Response
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

        $campaignData = $this->getCampaignInfo($campaignId);

        $recipients = [];
        foreach ($campaignData->data->campaign->xcoobee_targets as $xcoobee_target) {
            $recipients[] = ['xcoobee_id' => $xcoobee_target->xcoobee_id];
        }
        $recipients[] = ['xcoobee_id' => $xid];

        return $this->modifyCampaign($campaignId, [
            'reference' => $refId,
            'requests' => [],
            'targets' => [
                'xcoobee_ids' => $recipients,
            ],
        ], $config);
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
    
    protected function _getDefaultCampaignId()
    {
        $configuration = new Configuration();

        return $configuration->getConfig()->campaignId;
    }
}
