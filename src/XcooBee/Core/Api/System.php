<?php

namespace XcooBee\Core\Api;

use XcooBee\Http\Response;
use XcooBee\Core\Configuration;
use XcooBee\Exception\XcooBeeException;

class System extends Api 
{

    /** @var Users */
    protected $_users;

    /** @var Consent */
    protected $_consent;

    public function __construct() 
    {
        parent::__construct();

        $this->_users = new Users();
        $this->_consent = new Consents();
    }

    /**
     * method to check if pgp key and Campaign is correct.
     *
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function ping() 
    {
        $user = $this->_users->getUser();
        $response = new Response();
        if ($user->pgp_public_key) {
            $campaignInfo = $this->_consent->getCampaignInfo();
            if (!empty($campaignInfo->data->campaign)) {
                $response->code = 200;
            } else {
                $response->code = 400;
                $response->errors = [
                    (object) ['message' => "campaign not found."]
                ];
            }
        } else {
            $response->code = 400;
            $response->errors = [
                (object) ['message' => "pgp key not found."]
            ];
        }

        return $response;
    }
    
    /**
     * List all Events
     *
     * @param string $campaignId
     * @param array $config
     *  
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function listEventSubscriptions($campaignId = null, $config = []) 
    {
        if ($campaignId === null) {
            $campaignId = $this->_getDefaultCampaignId();
        }

        if (!$campaignId) {
            throw new XcooBeeException('No "campaignId" provided');
        }

        $query = 'query listEventSubscriptions($campaignId: String!) {
            event_subscriptions(campaign_cursor: $campaignId) {
                data {
                    event_type,
                    handler,
                    date_c 
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';

        return $this->_request($query, ['campaignId' => $campaignId], $config);
    }
    
    /**
     * add an Event
     *
     * @param array $events
     * @param string $campaignId
     * @param array $config
     *  
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addEventSubscription($events, $campaignId = null, $config = []) 
    {
        if ($campaignId === null) {
            $campaignId = $this->_getDefaultCampaignId();
        }

        if (!$campaignId) {
            throw new XcooBeeException('No "campaignId" provided');
        }

        $mutation = 'mutation addEventSubscription($config: AddSubscriptionsConfig!) {
            add_event_subscriptions(config: $config) {
                data{
                    event_type
                }
            }
        }';
        
        $mappedEvents = [];
        foreach ($events as $type => $handler) {
            $mappedEvents[] = [
                'handler' => $handler,
                'event_type' => $this->_getSubscriptionEvent($type)
            ];
        }

        return $this->_request($mutation, ['config' => [
                        'campaign_cursor' => $campaignId,
                        'events' => $mappedEvents,
                ]], $config);
    }

    /**
     * delete an Event
     *
     * @param array $events
     * @param string $campaignId
     * @param array $config
     *  
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteEventSubscription($events, $campaignId = null, $config = []) 
    {
        if ($campaignId === null) {
            $campaignId = $this->_getDefaultCampaignId();
        }

        if (!$campaignId) {
            throw new XcooBeeException('No "campaignId" provided');
        }

        $mutation = 'mutation deleteEventSubscription($config: DeleteSubscriptionsConfig!) {
            delete_event_subscriptions(config: $config) {
                deleted_number
            }
        }';
        
        $mappedEvents = [];
        foreach ($events as $key => $type) {
            $mappedEvents[] = $this->_getSubscriptionEvent($type);
        }

        return $this->_request($mutation, ['config' => [
                'campaign_cursor' => $campaignId,
                'events' => $mappedEvents,
            ]], $config);
    }

    /**
     * get events
     * 
     * @param string $event
     *
     * @return array 
     * @throws XcooBeeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _getSubscriptionEvent($event) 
    {
        $events = [
            'ConsentApproved'        => 'consent_approved',
            'ConsentDeclined'        => 'consent_declined',
            'ConsentChanged'         => 'consent_changed',
            'ConsentNear_expiration' => 'consent_near_expiration',
            'ConsentExpired'         => 'consent_expired',
            'DataApproved'           => 'data_approved',
            'DataDeclined'           => 'data_declined',
            'DataChanged'            => 'data_changed',
            'DataNear_expiration'    => 'data_near_expiration',
            'DataExpired'            => 'data_expired',
            'DreachPresented'        => 'breach_presented',
            'DreachBeeUsed'          => 'breach_bee_used',
        ];

        if (!array_key_exists($event, $events)) {
            throw new XcooBeeException('invalid "event" provided');
        }

        return $events[$event];
    }
}
