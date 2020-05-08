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
     * List event subscriptions
     *
     * @param string $referenceId
     * @param string $referenceType
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function listEventSubscriptions($referenceId = null, $referenceType = null, $config = [])
    {
        $query = 'query getEventSubscriptions($referenceType: EventReferenceType, $referenceId: String) {
            event_subscriptions (reference_type: $referenceType, reference_cursor: $referenceId) {
                data {
                    topic
                    channel
                    handler
                    status
                    event_type
                    reference_cursor
                    reference_type
                }
            }
        }';

        return $this->_request($query, [
            'referenceId' => $referenceId,
            'referenceType' => $referenceType,
        ], $config);
    }

    /**
     * List all possible event topics and channels
     *
     * @param string $referenceId
     * @param string $referenceType
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function getAvailableSubscriptions($referenceId = null, $referenceType = null, $config = [])
    {
        $query = 'query getAvailableSubscriptions($referenceType: EventReferenceType, $referenceId: String){
            available_subscriptions (reference_type: $referenceType, reference_cursor: $referenceId) {
                topic
                channels
            }
        }';

        return $this->_request($query, [
            'referenceId' => $referenceId,
            'referenceType' => $referenceType,
        ], $config);
    }

    /**
     * Add new event subscriptions
     *
     * @param array $eventSubscriptions
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function addEventSubscriptions($eventSubscriptions, $config = [])
    {
        $mutation = 'mutation addEventSubscriptions($config: AddSubscriptionsConfig!) {
            add_event_subscriptions(config: $config) {
                data {
                    topic
                    channel
                    handler
                }
            }
        }';

        foreach ($eventSubscriptions as $eventSubscription) {
            if (!array_key_exists('topic', $eventSubscription) || !array_key_exists('channel', $eventSubscription)) {
                throw new XcooBeeException('topic and channel should be provided');
            }
        }

        return $this->_request($mutation, ['config' => [
            'events' => $eventSubscriptions,
        ]], $config);
    }

    /**
     * Delete event subscriptions
     *
     * @param array $eventSubscriptions
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function deleteEventSubscriptions($eventSubscriptions, $config = [])
    {
        $mutation = 'mutation deleteEventSubscriptions($config: DeleteSubscriptionsConfig!) {
            delete_event_subscriptions(config: $config) {
                deleted_number
            }
        }';

        foreach ($eventSubscriptions as $eventSubscription) {
            if (!array_key_exists('topic', $eventSubscription) || !array_key_exists('channel', $eventSubscription)) {
                throw new XcooBeeException('topic and channel should be provided');
            }
        }

        return $this->_request($mutation, ['config' => [
            'events' => $eventSubscriptions,
        ]], $config);
    }

    /**
     * Insuspend event subscription
     *
     * @param string $topic
     * @param string $channel
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function unsuspendEventSubscription($topic, $channel, $config = [])
    {
        $mutation = 'mutation unsuspendEventSubscriptions($config: EditSubscriptionConfig!) {
            edit_event_subscription(config: $config) {
                topic
                channel
                status
            }
        }';

        return $this->_request($mutation, ['config' => [
            'topic' => $topic,
            'channel' => $channel,
            'status' => 'active',
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
                    topic
                    event_type
                    payload
                    hmac
                    date_c
                    response {
                        channel
                        status
                        response
                    }
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
     * @param string $topic
     * @param array $config
     *
     * @return Response
     * @throws XcooBeeException
     */
    public function triggerEvent($topic, $config = [])
    {
        $mutation = 'mutation sendTestEvent($topic: String){
            send_test_event(topic: $topic){
                topic
                payload
                hmac
            }
        }';

        return $this->_request($mutation, ['config' => [
            'topic' => $topic,
        ]], $config);
    }

    /**
     * Handle event subscriptions in webhooks.
     *
     * @param array $events
     *
     * @throws EncryptionException|XcooBeeException
     */
    public function handleEvents($events = [])
    {
        // If no events array is passed on, then parse the HTTP POST request and:
        // - validate HMAC hex digest if found,
        // - try to decrypt payload and
        // - create event object.
        if (empty($events)) {
            // Response data.
            $eventType = isset($_SERVER['HTTP_XBEE_EVENT']) ? $_SERVER['HTTP_XBEE_EVENT'] : null;
            $signature = isset($_SERVER['HTTP_XBEE_SIGNATURE']) ? $_SERVER['HTTP_XBEE_SIGNATURE'] : null;
            $handler = isset($_SERVER['HTTP_XBEE_HANDLER']) ? $_SERVER['HTTP_XBEE_HANDLER'] : null;
            $responseBody = file_get_contents('php://input', true);

            // Correctly escape new line characters so we can generate the correct HMAC hash.
            $payload = $responseBody;
            $payload = str_replace('\n', "\n", $payload);
            $payload = str_replace('\r', "\r", $payload);

            // Validate signature if found.
            if (!is_null($signature) && !empty($payload)) {
                // Use XcooBee Id as the HMAC secret key.
                $xid = $this->_xcoobee->users->getUser()->xcoobeeId;

                // Generate HMAC hash.
                $hmac = hash_hmac('sha1', $payload, $xid);

                if (!hash_equals($hmac, $signature)) {
                    throw new EncryptionException('Invalid signature');
                }
            }

            // Try to decrypt the payload.
            if (!empty($payload)) {
                try {
                    $decryptedPayload = $this->_encryption->decrypt($payload);

                    if ($decryptedPayload !== null) {
                        $payload = $decryptedPayload;
                    }
                } catch (EncryptionException $e) {
                    // Do nothing, we will pass on the payload as it is.
                }
            }

            // Create event object.
             $events[0] = (object) [
                'handler' => $handler,
                'payload' => $payload,
            ];
        }

        // Process event objects.
        foreach ($events as $event) {
            // Call the handler function and pass on the payload.
            if (isset($event->handler) && isset($event->payload)) {
                if (!is_callable($event->handler)) {
                    throw new XcooBeeException('The handler function does not exist');
                }

                call_user_func_array($event->handler, array($event->payload));
            }
        }
    }
}
