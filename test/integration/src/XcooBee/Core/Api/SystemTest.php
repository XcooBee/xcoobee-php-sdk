<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class SystemTest extends IntegrationTestCase
{

    public function testPing()
    {
        $response = self::$xcoobee->system->ping();
        $this->assertEquals(200, $response->code);
        $this->assertEquals(null, $response->result);
    }

    public function testAddEventSubscription()
    {
        $response = self::$xcoobee->system->addEventSubscription(['UserDataRequest' => 'testEventHandler']);
        $this->assertEquals(200, $response->code);
        $this->assertEquals((object) ['event_type' => 'user_data_request'], $response->result->add_event_subscriptions->data[0]);
    }
    
    public function testListEventSubscriptions()
    {
        $response = self::$xcoobee->system->listEventSubscriptions();
        $this->assertEquals(200, $response->code);
        $this->assertEquals('user_data_request', $response->result->event_subscriptions->data[0]->event_type);
        $this->assertEquals('testEventHandler', $response->result->event_subscriptions->data[0]->handler);
    }
    
    public function testDeleteEventSubscription()
    {
        $response = self::$xcoobee->system->deleteEventSubscription(['UserDataRequest']);
        $this->assertEquals(200, $response->code);
        $this->assertEquals((object) ['deleted_number' => 1], $response->result->delete_event_subscriptions);
    }

    public function testGetEvents()
    {
        $response = self::$xcoobee->system->getEvents();
        $this->assertEquals(200, $response->code);
    }

}
