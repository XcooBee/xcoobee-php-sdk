<?php

namespace XcooBee\Core\Api;

use XcooBee\Core\Encryption;
use XcooBee\Exception\EncryptionException;
use XcooBee\Http\Response;
use XcooBee\Exception\XcooBeeException;
use XcooBee\XcooBee;

class System extends Api 
{
    /** @var Encryption */
    protected $_encryption;

    public function __construct(XcooBee $xcoobee)
    {
        $this->_encryption = new Encryption($xcoobee);

        parent::__construct($xcoobee);
    }

    /**
     * method to check if pgp key and Campaign is correct.
     *
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function ping($config = []) 
    {
        $user = $this->_xcoobee->users->getUser($config);
        $response = new Response();
        if ($user->pgp_public_key) {
            $campaignInfo = $this->_xcoobee->consents->getCampaign(null, $config);
            if (!empty($campaignInfo->result->campaign)) {
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
     * @return Response
     * @throws XcooBeeException
     */
    public function listEventSubscriptions($campaignId = null, $config = []) 
    {
        $campaignId = $this->_getCampaignId($campaignId, $config);
        
        $query = 'query listEventSubscriptions($campaignId: String!) {
            event_subscriptions(campaign_cursor: $campaignId) {
                data {
                    event_type,
                    handler,
                    date_c 
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
     * @return Response
     * @throws XcooBeeException
     */
    public function addEventSubscription($events, $campaignId = null, $config = []) 
    {
        $campaignId = $this->_getCampaignId($campaignId, $config);
        
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
     * @return Response
     * @throws XcooBeeException
     */
    public function deleteEventSubscription($events, $campaignId = null, $config = []) 
    {
        $campaignId = $this->_getCampaignId($campaignId, $config);
        
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
     * get all events
     *
     * @param array $config
     *  
     * @return Response
     * @throws XcooBeeException
     */
    public function getEvents($config = [])
    {
        $query = 'query getEvents($userId: String!) {
            events(user_cursor: $userId) {
                data {
                    event_id
                    reference_cursor
                    reference_type
                    owner_cursor
                    event_type
                    payload
                    hmac
                    date_c
                }
            }
        }';

        $events = $this->_request($query, ['userId' => $this->_getUserId($config)], $config);

        if ($events->code != 200) {
            return $events;
        }

        foreach ($events->result->events->data as $key => $event) {
            try {
                $payload = $this->_encryption->decrypt($event->payload);

                if ($payload === null) {
                    $response = new Response();
                    $response->code = 400;
                    $response->errors = [
                        (object)['message' => 'can\'t decrypt pgp encrypted message, check your keys'],
                    ];

                    return $response;
                }

                $events->result->events->data[$key]->payload = json_decode($payload);
            } catch (EncryptionException $e) {
                // do nothing, because we cannot decrypt value, send to user encrypted
            }
        }

        return $events;
    }
    
    /**
     * get events
     * 
     * @param string $event
     *
     * @return array 
     * @throws XcooBeeException
     */
    protected function _getSubscriptionEvent($event) 
    {
        $events = [
            'ConsentApproved'        => 'consent_approved',
            'ConsentDeclined'        => 'consent_declined',
            'ConsentChanged'         => 'consent_changed',
            'ConsentNearExpiration'  => 'consent_near_expiration',
            'ConsentExpired'         => 'consent_expired',
            'DataApproved'           => 'data_approved',
            'DataDeclined'           => 'data_declined',
            'DataChanged'            => 'data_changed',
            'DataNearExpiration'     => 'data_near_expiration',
            'DataExpired'            => 'data_expired',
            'BreachPresented'        => 'breach_presented',
            'BreachBeeUsed'          => 'breach_bee_used',
            'UserDataRequest'        => 'user_data_request',
            'UserMessage'            => 'user_message',
            'BeeSuccess'             => 'bee_success',
            'BeeError'               => 'bee_error',
            'ProcessSuccess'         => 'process_success',
            'ProcessError'           => 'process_error',
            'ProcessFileDelivered'   => 'process_file_delivered',
            'ProcessFilePresented'   => 'process_file_presented',
            'ProcessFileDownloaded'  => 'process_file_downloaded',
            'ProcessFileDeleted'     => 'process_file_deleted'
        ];

        if (!array_key_exists($event, $events)) {
            throw new XcooBeeException('invalid "event" provided');
        }

        return $events[$event];
    }
}
