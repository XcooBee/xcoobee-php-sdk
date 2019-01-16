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
            $campaignInfo = $this->_xcoobee->consents->getCampaignInfo(null, $config);
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
     * trigger test event to campaign webhook
     *
     * @param string $type
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function triggerEvent($type, $config = [])
    {
        $campaignId = $this->_getCampaignId(null, $config);

        $mutation = 'mutation sendTestEvent($campaignId: String!, $type: EventSubscriptionType!){
            send_test_event(campaign_cursor: $campaignId, type: $type){
                event_type
                payload
                hmac
            }
        }';

        return $this->_request($mutation, ['config' => [
            'campaign_cursor' => $campaignId,
            'type' => $this->_getSubscriptionEvent($type),
        ]], $config);
    }

    /*
     * Handle event subscriptions in webhooks.
     *
     * @param array $events
     *
     * @return void
     * @throws EncryptionException|XcooBeeException;
     */
    public function handleEvents($events = [])
    {
        // If no events array is passed, then parse the HTTP POST request and:
        // - validate delivery signature if found,
        // - try to decrypt payload and
        // - create an event object.
        if (empty($events)) {
            // Response data.
            $eventType = isset($_SERVER['HTTP_X_XBEE_EVENT']) ? $_SERVER['HTTP_X_XBEE_EVENT'] : null;
            $eventId  = isset($_SERVER['HTTP_X_TRANS_ID']) ? $_SERVER['HTTP_X_TRANS_ID'] : null;
            $signature = isset($_SERVER['HTTP_X_XBEE_SIGNATURE']) ? $_SERVER['HTTP_X_XBEE_SIGNATURE'] : null;
            $responseBody = file_get_contents('php://input', true);

            /*
             * Get the exact payload string to correctly calculate the HMAC hex digest:
             * - Remove `{"data:"}` and `"}` from the response.
             * - Correctly escape new line characters.
             */
            $payload = trim(trim($responseBody, '{"data":"'), '"}');
            $payload = str_replace('\n', "\n", $payload);
            $payload = str_replace('\r', "\r", $payload);

            // Validate delivery signature if found.
            if (!empty($signature) && !empty($payload)) {
                $config = $this->_xcoobee->getConfig();

                if (!$config->pgpSecret) {
                    throw new EncryptionException('PGP private key not provided');
                }

                // Validate signature.
                $xcoobee_id = $this->_xcoobee->users->getUser()->xcoobeeId;
                if ($signature !== hash_hmac( 'sha1', $payload, $xcoobee_id)) {
                    throw new EncryptionException('Invalid signature');
                }
            }

            // Try to decrypt payload.
            if(!empty($eventType) && !empty($payload)) {
                try {
                    $payload = $this->_encryption->decrypt($payload);

                    if ($payload !== null) {
                        // Create event object.
                        $events[0] = (object) [
                            'event_type' => $this->_getSubscriptionEvent($eventType),
                            'payload' => json_decode($payload),
                        ];
                    }
                } catch (EncryptionException $e) {
                    // Do nothing.
                }
            }
        }

        // Process events and call the handler function and pass the payload to it.
        foreach ($events as $event) {
            $consentData = $this->_xcoobee->consents->getConsentData($event->payload->consentId);
            
            if ($consentData->code !== 200) {
                throw new XcooBeeException('Could not get campaign data');
            }

            $campaignId = $consentData->result->consent->campaign_cursor;

            $handler = $this->_getEventHandler($campaignId, $event->event_type);

            // Call handler and pass payload to it.
            if (!is_null($handler)) {
                call_user_func_array($handler, array($event->payload));
            }
        }
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

    /**
     * Returns the handler of an event subscription.
     *
     * @param string $campaignId
     * @param string $event
     * @param array $config
     *
     * @return string|null
     */
    protected function _getEventHandler($campaignId, $event, $config = [])
    {
        $response = $this->listEventSubscriptions($campaignId, $config);
        $subscriptions = $response->result->event_subscriptions->data;

        $subscription  = array_map(function($s) use ($event) {
            if ($s->event_type === $event) {
                return $s->handler;
            } 
        }, $subscriptions);

        if (!empty($subscription)) {
            return $subscription[0];
        }

        return null;
    }
}
