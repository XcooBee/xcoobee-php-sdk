<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class UsersTest extends IntegrationTestCase
{

    public function testGetUser()
    {
        $user = $this->_xcoobee->users->getUser();
        $this->assertEquals('AvPfoQD54o6bJ5t2CblzenSrwp8COIZL/JJxAjNGCGK1EKV+1lxtybvnQE9iJqLmVyRQKg==', $user->userId);
        $this->assertEquals('~ganesh_', $user->xcoobeeId);
    }

    public function testGetConversations()
    {
        $response = $this->_xcoobee->users->getConversations();
        $keys = array_keys($response->data->conversations->data);
        $this->assertEquals('200', $response->code);
        $this->assertEquals((object)[
            'display_name' => 'test test122',
            'consent_cursor' => null,
            'target_cursor' => 'AvPfoQD544/OdJ0lDu9zenf0kp8Ca4Qc/JN+BWJGDG3lGqd81FU7zem0QE9iJqLmVyRQKg==',
            'date_c' => '2018-05-09T09:33:53Z'
        ], $response->data->conversations->data[end($keys)]);
    }
    
    public function testSendUserMessage()
    {
        $response = $this->_xcoobee->users->sendUserMessage("test message", 'AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals('200', $response->code);
        $this->assertEquals((object)['note_text' => "test message"], $response->data->send_message);
    }
    
    public function testGetConversation()
    {
        $response = $this->_xcoobee->users->getConversation('AvPfoQD54o6bJ5t2CblzenSrwp8COIZL/JJxAjNGCGK1EKV+1lxtybvnQE9iJqLmVyRQKg==');
        $this->assertEquals('200', $response->code);
    }
    
}
