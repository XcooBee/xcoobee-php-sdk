<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class SystemTest extends IntegrationTestCase
{
    public function testPing()
    {
        $response = $this->_xcoobee->system->ping();
        $this->assertEquals(200, $response->code);
        $this->assertEquals(null, $response->data);
    }
    
    public function testListEventSubscriptions()
    {
        $response = $this->_xcoobee->system->listEventSubscriptions();
        $this->assertEquals(200, $response->code);
        $this->assertEquals((object)[
            'event_type' => 'consent_approved',
            'handler' => 'testHandler',
            'date_c' => '2018-05-23T08:02:25Z'
        ], $response->data->event_subscriptions->data[0]);
    }

    public function testAddEventSubscription()
    {
        $response = $this->_xcoobee->system->addEventSubscription(['UserDataRequest' => 'testEventHandler']);
        $this->assertEquals(200, $response->code);
        $this->assertEquals((object)['event_type' => 'user_data_request'], $response->data->add_event_subscriptions->data[0]);
    }

    public function testDeleteEventSubscription()
    {
        $response = $this->_xcoobee->system->deleteEventSubscription(['UserDataRequest']);
        $this->assertEquals(200, $response->code);
        $this->assertEquals((object)['deleted_number' => 1], $response->data->delete_event_subscriptions);
    }
    
    public function testGetEvents()
    {
       $response = $this->_xcoobee->system->getEvents();
       print_r($response);
    }
    
}
