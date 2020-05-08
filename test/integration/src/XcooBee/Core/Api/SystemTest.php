<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class SystemTest extends IntegrationTestCase
{
    public function testPing()
    {
        $response = self::$xcoobee->system->ping();
        $this->assertEquals(400, $response->code);
        $this->assertEquals('pgp key not found.', $response->errors[0]->message);
    }

    public function testGetAvailableSubscriptions()
    {
        $response = self::$xcoobee->system->getAvailableSubscriptions();
        $this->assertEquals(200, $response->code);
        $this->assertEquals('campaign:' . self::$campaign->campaign_reference . '/*', $response->result->available_subscriptions[0]->topic);
        $this->assertEquals(['email', 'webhook', 'inbox'], $response->result->available_subscriptions[0]->channels);
    }

    public function testAddEventSubscriptions()
    {
        $response = self::$xcoobee->system->addEventSubscriptions([[
            'topic' => 'campaign:' . self::$campaign->campaign_reference . '/data_changed',
            'channel' => 'webhook',
            'handler' => 'testEventHandler',
        ]]);
        $this->assertEquals(200, $response->code);
        $this->assertEquals((object) [
            'topic' => 'campaign:' . self::$campaign->campaign_reference . '/data_changed',
            'channel' => 'webhook',
            'handler' => 'testEventHandler',
        ], $response->result->add_event_subscriptions->data[0]);
    }

    public function testListEventSubscriptions()
    {
        $response = self::$xcoobee->system->listEventSubscriptions();
        $this->assertEquals(200, $response->code);

        $this->assertEquals('campaign:' . self::$campaign->campaign_reference . '/data_changed', $response->result->event_subscriptions->data[0]->topic);
        $this->assertEquals('webhook', $response->result->event_subscriptions->data[0]->channel);
        $this->assertEquals('active', $response->result->event_subscriptions->data[0]->status);
        $this->assertEquals('data_changed', $response->result->event_subscriptions->data[0]->event_type);
        $this->assertEquals('testEventHandler', $response->result->event_subscriptions->data[0]->handler);

        $this->assertEquals('campaign:' . self::$campaign->campaign_reference . '/data_approved', $response->result->event_subscriptions->data[1]->topic);
        $this->assertEquals('email', $response->result->event_subscriptions->data[1]->channel);
        $this->assertEquals('active', $response->result->event_subscriptions->data[1]->status);
        $this->assertEquals('data_approved', $response->result->event_subscriptions->data[1]->event_type);
        $this->assertEquals(null, $response->result->event_subscriptions->data[1]->handler);
    }

    public function testDeleteEventSubscriptions()
    {
        $response = self::$xcoobee->system->deleteEventSubscriptions([[
            'topic' => 'campaign:' . self::$campaign->campaign_reference . '/data_changed',
            'channel' => 'webhook',
        ]]);
        $this->assertEquals(200, $response->code);
        $this->assertEquals((object) ['deleted_number' => 1], $response->result->delete_event_subscriptions);
    }

    public function testGetEvents()
    {
        $response = self::$xcoobee->system->getEvents();
        $this->assertEquals(200, $response->code);
    }
}
